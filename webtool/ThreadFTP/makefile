DIR		:= ./debug
EXE		:= $(DIR)/$(shell basename `pwd`)
CC		:= g++ 
LIBDIR	:= 
LIBS	:=  -lboost_system -lboost_filesystem
INC		:=
SRCS	:= $(wildcard *.cpp) $(wildcard *.c) $(wildcard *.cc)
OCPP	:= $(patsubst %.cpp, $(DIR)/%.o, $(wildcard *.cpp))
OC		:= $(patsubst %.c, $(DIR)/%.co, $(wildcard *.c))
OCC		:= $(patsubst %.cc, $(DIR)/%.cco, $(wildcard *.cc))
OBJS	:= $(OC) $(OCC) $(OCPP)
RM		:= rm -rf
CXXFLAGS	:= -std=c++11
start	: mkdebug $(EXE) 
mkdebug :
	@if [ ! -d $(DIR) ]; then mkdir $(DIR); fi;
$(EXE) 	: $(OBJS)
	$(CC) -static -Wl,--whole-archive -lpthread -Wl,--no-whole-archive -o $@ $^  $(LIBS)
$(DIR)/%.o : %.cpp
	$(CC) $(CXXFLAGS) -c $^   -o $@
$(DIR)/%.co : %.c
	$(CC) $(CXXFLAGS) -c $^   -o $@
$(DIR)/%.cco : %.cc
	$(CC) $(CXXFLAGS) -c $^   -o $@
.PHONY 	: clean rmdebug rebuild 
clean	:
	@$(RM) $(EXE) $(DIR)/*.o $(DIR)/*.co $(DIR)/*.cco
rmdebug	:
	@$(RM) $(DIR)
rebuild: clean start
