# windows进程线程间同步与通信
> 2018-10-8 23:44
> windows 线程 进程 同步

> &copy;Power By [ojisuan.com(o计算)](http://www.ojisuan.com"http://www.ojisuan.com") 作者: [Ooo]("qq:978831608")

------

## windows进程线程间同步的几种方式

- 临界区(critical_section)
- 互斥量(mutex)
- 信号量(semaphore)
- 事件（Event）

## windows临界区：用于线程间的同步

**主要有一个定义、四个函数：**

- CRITICAL_SECTION g_sect;//定义一个临界区块
- InitializeCriticalSection://初始化一个临界区
- EnterCriticalSection://等待进入一个临界区并获得所有权
- LeaveCriticalSection: //离开一个临界区，释放临界区所有权
- DeleteCriticalSection: //释放临界区

**临界区用法：**

一般按照如下步骤：

```
CRITICAL_SECTION g_sect;
InitializeCriticalSection(&g_sect);
CreateThread proc1;
CreateThread Proc2;
......       //create多个线程
DeleteCriticalSection(&g_sect);
```	

其中proc1和proc2函数中操作步骤：

```
void  proc1(void* p)
{
    EnterCriticalSection(&g_sect);
    operation;//执行一些同步操作
    LeaveCriticalSection(&g_sect);
}
```
具体例子如下：

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
    int nNumb;
    char sName[256];
    float fSalary;
};
CRITICAL_SECTION g_sect;
int m_nCount = 0;//用于线程计数
bool g_bRun = true;//用于线程通信，理论上不安全，这里先凑合着，通信后面会讲到
list<SInfo> glist;
void addProc(void* p)
{
    m_nCount++;
    while (g_bRun)
        if (glist.size() < 100)
        {
            EnterCriticalSection(&g_sect);
            glist.push_back({ 1083,"ags ",9832.5f });
            cout << "push_back" << endl;
            LeaveCriticalSection(&g_sect);

        }
    cout << "exit" << endl;

    m_nCount--;
}
void decProc(void* p)
{
    m_nCount++;

    while (g_bRun)
        if (glist.size() > 99)
        {
            EnterCriticalSection(&g_sect);
            glist.pop_back();
            cout << "pop_back" << endl;
            LeaveCriticalSection(&g_sect);
        }
    cout << "exit" << endl;
    m_nCount--;
}
int main()
{
	InitializeCriticalSection(&g_sect);

	int i =-1;
	while (++i < 10)
	{
		_beginthread(addProc, 0, 0);
		_beginthread(decProc, 0, 0);
	}
	cin >> i;//阻塞一下，等待线程创建完成
	g_bRun = false;//让线程开始退出
	while (m_nCount)
		Sleep(16);
	DeleteCriticalSection(&g_sect);
	return 0;
}
```
上面用全局变量做线程间通信并不安全，考虑引入volatile与c++11原子变量：

- volatile:只保证“可见性”，不保证“原子性”[参考链接](https://www.cnblogs.com/howo/p/8525665.html)
- c++11原子变量:能很好的解决线程通信问题，但是速度相对较慢

```
#include <windows.h>
#include <time.h>
#include <stdio.h>
#include <atomic>
std::atomic_flag g_bRun;
std::atomic<int> tet(0);
//int  tet(0);
//volatile tet(0);
const unsigned num =10000000;//增大num，增加出错几率
CRITICAL_SECTION g_sect;

DWORD _stdcall proc1(void* p)
{
    time_t tt = time(NULL);

    int i = 0;
    while (i < num)
    {
        EnterCriticalSection(&g_sect);
        tet += 2;
        LeaveCriticalSection(&g_sect);

        ++i;
    }
    printf("%d-%d\n", 1, time(NULL) - tt);
    return 0;
}

DWORD _stdcall proc2(void* p)
{
	time_t tt = time(NULL);
	int i = 0;
	while (i < num)
	{
		EnterCriticalSection(&g_sect);

		tet += 3;
		LeaveCriticalSection(&g_sect);

		++i;
	}
	printf("%d-%d\n", 2,  time(NULL) - tt);
	return 0;
}

int main(int argc, char* argv[])
{
	InitializeCriticalSection(&g_sect);

	CreateThread(0, 0, proc1, 0, 0, 0);
	CreateThread(0, 0, proc2, 0, 0, 0);

	system("pause");
	printf("%d\n", tet);

	system("pause");
	DeleteCriticalSection(&g_sect);
	return 0;
}
```

## Windows mutex用于线程或者进程间互斥

**mutex特点:**

- 是内核对象,并记录所有使用者
- 可以用于线程,进程间互斥
- 互斥对象名唯一标识互斥对象,名字相同,互斥对象必然相同,一对一映射

**mutex和临界区比较:**

- 临界区不能用于进程间互斥,他属于用户方式的对象,仅能用于线程间互斥,但是运行速度较快.而mutex是内核对象,速度较慢,但可以用于进程和线程间互斥
- 临界区和mutex一样,必须等到release才可以再次使用
- 由于mutex记录了每个使用他的对象,所以可以处理"遗弃"问题

**mutex用法步骤：**

```
g_ThreadMutex = CreateMutex (NULL , false,( LPCWSTR )"AAA" );//true的话是自己获得所有权，要执行ReleaseMutex之后才能对其它线程开放权限
WaitForSingleObject( g_ThreadMutex ,INFINITE ); //第二个参数为等待的时间，INFINITE为极大值，其实是-1
ReleaseMutex( g_ThreadMutex );  //释放
g_ThreadMutex = OpenMutex (MUTEX_ALL_ACCESS , true,( LPCWSTR) "AAA" );  //获取AAA标识符的线程句柄
WaitForSingleObject( g_ThreadMutex ,INFINITE );  //等待
ReleaseMutex( g_ThreadMutex ); //释放
CloseHandle(g_ThreadMutex);//关闭
```

**具体例子：**

* 第一个例子的替代品，只是把临界区换成了mutex
* OpenMutex函数可以依据 CreateMutex函数创建时带入的字符串Id标识符获取mutex句柄

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<SInfo> glist;
void addProc(void* p)
{
	m_nCount++;
	HANDLE hMutex = OpenMutex(MUTEX_ALL_ACCESS, TRUE, "AAA");
	if (!hMutex)
	{
		cout << "互斥量获取失败！" << endl;
		return;
	}
	while (g_bRun)
		if (glist.size() < 100)
		{
			WaitForSingleObject(hMutex, INFINITE);
			glist.push_back({ 1083,"ags ",9832.5f });
			cout << "push_back " << (int&)p<< endl;
			ReleaseMutex(hMutex);
		}
	cout << "exit" << endl;

	m_nCount--;
}
void decProc(void* p)
{
	m_nCount++;
	HANDLE hMutex = OpenMutex(MUTEX_ALL_ACCESS, TRUE, "AAA");
	if (!hMutex)
	{
		cout << "互斥量获取失败！" << endl;
		return;
	}
	while (g_bRun)
		if (glist.size() > 99)
		{
			WaitForSingleObject(hMutex, INFINITE);
			glist.pop_back();
					cout << "pop_back = "<<(int&)p << endl;
			ReleaseMutex(hMutex);

		}
	cout << "exit" << endl;
	m_nCount--;
}
int main()
{
	HANDLE hMutex = CreateMutex(NULL, TRUE, "AAA");

	int i =-1;
	while (++i < 10)
	{
		_beginthread(addProc, 0, (void*)i);
		_beginthread(decProc, 0, (void*)(i+10));
	}
	ReleaseMutex(hMutex);

	cin >> i;
	g_bRun = false;
	while (m_nCount)
		Sleep(16);

	CloseHandle(hMutex);
	return 0;
}
```

进程间的互斥

```
#include <windows.h>
#include <iostream>
using namespace std;
DWORD GetPid()//这个函数也可以用API函数GetCurrentProcessId()直接替代
{
	HANDLE h = GetCurrentProcess();//其实返回的是-1
	return GetProcessId(h);//测试把(HANDL*)-1直接带入也可以获取到Id值
}
int main()
{
	HANDLE hMutex = OpenMutex(MUTEX_ALL_ACCESS, TRUE, "AAA");
	if(!hMutex)
	{
		hMutex = CreateMutex(NULL, FALSE, "AAA");

		if (!hMutex)
		{
			cout << "Mutex create failed!" << endl;
			return -1;
		}
	}
	while (TRUE)
	{
		DWORD dw = GetTickCount();
		dw = WaitForSingleObject(hMutex, 1000);
	  //cout << "I am " << GetCurrentProcessId() << endl;
	  //	cout << "I am " << GetPid() << endl;
	  //cout << "I am " << GetTickCount()-dw << endl;
		cout << "Wait =" << dw << endl; 
	  //ReleaseMutex(hMutex);
	}

	return 0;
}
```

**Sleep与GetTickCount：都是Windows API**

- time是C语言库函数，得到秒为单位的时间（起点是1970.1.1）
- GetTickCount，得到毫秒为单位，起点是开机。

进程单例实现:

```
#include <Windows.h>
#include <iostream>
using namespace std;
bool IsExit()
{
	HANDLE hMutex = OpenMutex(MUTEX_ALL_ACCESS, TRUE, "AAA");
	if (hMutex)
	{
		CloseHandle(hMutex);
		return true;
	}
	hMutex = CreateMutex(NULL, FALSE, "AAA");

	if (!hMutex)
		cout << "Mutex create failed!" << endl;
	return false;
}
int main()
{
	if (IsExit())
	{
		cout << "已经启动了，不能启动两份进程~！" << endl;
		return -1;
	}
	while (true)
	{
		cout << "Menu" << endl;
		Sleep(1000);
	}
	return 0;
}
```

## Windows线程间通信：

- 全局变量法（atomic）【上面已经提到过】
- windows事件法（Event）
- Windows线程消息（PostMessage）

**WaitForSingleObject 能接受的对象类型**

1. Change notification 
2. Console input 
3. Event 
4. Memory resource notification 
5. Mutex 
6. Process 
7. Semaphore 
8. Thread （beginthread和CreateThread的返回值都可以用来Wait）
9. Waitable timer

## windows事件法（Event）

    HANDLE WINAPI CreateEvent(
      _In_opt_  LPSECURITY_ATTRIBUTES lpEventAttributes,
      _In_      BOOL bManualReset,//手动(要自己reset)，自动
      _In_      BOOL bInitialState,//初始化有无信号
      _In_opt_  LPCTSTR lpName//给OpenEvent()用;
    );

**设置信号：**

BOOL WINAPI SetEvent(HANDLE hEvent);

**清理信号：** 

BOOL WINAPI ResetEvent (HANDLE hEvent);

**几种实现方式**

1. sleep模仿方式

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<HANDLE> glist;

void theProc(void* p)
{
	auto h = CreateMutex(NULL, TRUE, NULL);
	glist.push_back(h);
	

	while (g_bRun)
	{
		cout <<(int&)p<<":"<< glist.size() << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
	ReleaseMutex(h);
}
int main()
{
	int i = -1;
	while (++i < 10)
	{
		_beginthread(theProc, 0, (void*)i);
		Sleep(16);
	}
		


	cin >> i;
	g_bRun = false;
	
	while (glist.size())
	{
		auto it  = glist.begin();
		HANDLE h = *it;
		WaitForSingleObject(h,INFINITE);
		glist.erase(it);
	}

	return 0;
}
```
2. Enent方式

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<HANDLE> glist;

void theProc(HANDLE hEvent)
{
	auto h = CreateMutex(NULL, TRUE, NULL);
	glist.push_back(h);
	SetEvent(hEvent);
	while (g_bRun)
	{
		cout <<(int&)hEvent<<":"<< glist.size() << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
	ReleaseMutex(h);
}
int main()
{
	int i = -1;
	HANDLE hEvent = CreateEvent(NULL, FALSE, FALSE, NULL);//
	while (++i < 10)
	{
		_beginthread(theProc, 0, hEvent);
		WaitForSingleObject(hEvent, INFINITE);
	}
		


	cin >> i;
	g_bRun = false;
	
	while (glist.size())
	{
		auto it  = glist.begin();
		HANDLE h = *it;
		WaitForSingleObject(h,INFINITE);
		glist.erase(it);
	}

	return 0;
}
```

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<HANDLE> glist;

void theProc(HANDLE hEvent)
{
	WaitForSingleObject(hEvent, INFINITE);
	auto h = CreateMutex(NULL, TRUE, NULL);
	glist.push_back(h);
	SetEvent(hEvent);

	while (g_bRun)
	{
		cout <<(int&)hEvent<<":"<< glist.size() << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
	ReleaseMutex(h);
}
int main()
{
	int i = -1;
	HANDLE hEvent = CreateEvent(NULL, FALSE, TRUE, NULL);
	while (++i < 10)
	{
		_beginthread(theProc, 0, hEvent);
	}
		


	cin >> i;
	g_bRun = false;
	
	while (glist.size())
	{
		auto it  = glist.begin();
		HANDLE h = *it;
		WaitForSingleObject(h,INFINITE);
		glist.erase(it);
	}

	return 0;
}
```

WaitForSingleObject函数接受线程对象

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<HANDLE> glist;
void theProc(void* p)
{
	HANDLE h = GetCurrentThread();
	while (g_bRun)
	{
		cout <<(int&)p<<":"<< m_nCount << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;

	m_nCount--;
}
int main()
{
	int i = -1;
	while (++i < 10)
	{
		HANDLE h = (HANDLE)_beginthread(theProc, 0, (void*)i);
		glist.push_back(h);
	}

	cin >> i;
	g_bRun = false;
	
	while (glist.size())
	{
		HANDLE h = glist.back();
		WaitForSingleObject(h,INFINITE);
		glist.pop_back();
	}
	
	return 0;
}
```

WaitForMultipleObjects的使用

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;

void theProc(void* p)
{
	++m_nCount;
	while (g_bRun)
	{
		cout << (int&)p << ":" << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
}
int main()
{
	int i = -1;
	HANDLE hs[10];
	while (++i < 10)
		hs[i] = (HANDLE)_beginthread(theProc, 0,(void*)i);

	cin >> i;
	g_bRun = false;

	WaitForMultipleObjects(_countof(hs), hs, TRUE, INFINITE);//接受线程对象

	return 0;
}
```

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;

void theProc(HANDLE hEvent)
{
	++m_nCount;
	while (g_bRun)
	{
		cout <<(int&)hEvent<<":" << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
	SetEvent(hEvent);
}
int main()
{
	int i = -1;
	HANDLE hs[10];
	while (++i < 10)
	{
		hs[i] = CreateEvent(NULL, FALSE, FALSE, NULL);
		_beginthread(theProc, 0, hs[i]);
	}
	cin >> i;
	g_bRun = false;

	WaitForMultipleObjects(_countof(hs), hs,TRUE,INFINITE);//接受事件

	return 0;
}
```

线程定时任务

```
#include <iostream>
#include <list>
#include <process.h>
#include <windows.h>
using namespace std;
struct SInfo
{
	int nNumb;
	char sName[256];
	float fSalary;
};
int m_nCount = 0;
bool g_bRun = true;
list<HANDLE> glist;

void theProc(HANDLE hEvent)
{
	while (g_bRun)
	{
		WaitForSingleObject(hEvent, INFINITE);//挂起线程
		cout <<(int&)hEvent<<":"<< glist.size() << endl;
		Sleep(1000);
	}
	cout << "exit" << endl;
	m_nCount--;
}
int main()
{
	int i = -1;
	HANDLE hs[10];
	while (++i < 10)
	{
		hs[i]= CreateEvent(NULL, FALSE, FALSE, NULL);
		_beginthread(theProc, 0, hs[i]);
	}
	while (i > -1)
	{
		cout << "请输入0-9工作线程编号：";
		cin >> i;
		if (i >= 0 && i < 10)
			SetEvent(hs[i]);

	}
	g_bRun = false;
	return 0;
}
```
## windows信号量

```
#include <stdio.h>
#include <process.h>
#include <windows.h>
long g_nNum;
unsigned int __stdcall Fun(void *pPM);
const int THREAD_NUM = 10;
//信号量与关键段
HANDLE            g_hThreadParameter;
CRITICAL_SECTION  g_csThreadCode;
int main()
{
	//初始化信号量和关键段
	g_hThreadParameter = CreateSemaphore(NULL, 0, 1, NULL);//当前0个资源，最大允许1个同时访问
	InitializeCriticalSection(&g_csThreadCode);

	HANDLE  handle[THREAD_NUM];
	g_nNum = 0;
	int i = 0;
	while (i < THREAD_NUM)
	{
		handle[i] = (HANDLE)_beginthreadex(NULL, 0, Fun, &i, 0, NULL);
		WaitForSingleObject(g_hThreadParameter, INFINITE);//等待信号量>0
		++i;
	}
	WaitForMultipleObjects(THREAD_NUM, handle, TRUE, INFINITE);

	//销毁信号量和关键段
	DeleteCriticalSection(&g_csThreadCode);
	CloseHandle(g_hThreadParameter);
	for (i = 0; i < THREAD_NUM; i++)
		CloseHandle(handle[i]);
	return 0;
}
unsigned int __stdcall Fun(void *pPM)
{
	int nThreadNum = *(int *)pPM;
	ReleaseSemaphore(g_hThreadParameter, 1, NULL);//信号量++
	Sleep(50);//some work should to do
	EnterCriticalSection(&g_csThreadCode);
	++g_nNum;
	Sleep(0);//some work should to do
	printf("线程编号为%d  全局资源值为%d\n", nThreadNum, g_nNum);
	LeaveCriticalSection(&g_csThreadCode);
	return 0;
}
```
```
#include <process.h>
#include <Windows.h>
#include <iostream>
using namespace std;
HANDLE g_sem;
bool g_bRun = true;
void theProc(void* p)
{
	while (g_bRun)
	{
		WaitForSingleObject(g_sem, INFINITE);
		cout << int(p) << endl;
		Sleep(1000);
		ReleaseSemaphore(g_sem, 1, NULL);
	}

}
int main()
{
	g_sem = CreateSemaphore(NULL, 0, 10, NULL);
	if (!g_sem)
		return -1;
	int i = -1;
		long nCount = 0;
	while (++i < 20)
		_beginthread(theProc, 0, (void*)i);

	while (true)
	{
		cin >> i;
		if (i < 10 && i>0)
			ReleaseSemaphore(g_sem, i, &nCount);
	}
	return 0;
}
```

> * 作者：Ooo  -- QQ:`978831608`
> * 邮箱：<978831608@qq.com>
> * 链接：[o计算](http://www.ojisuan.com"http://www.ojisuan.com")
> * 转载请注明出处。