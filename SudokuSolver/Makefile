# Makefile for Assignment2
# Ryan Osler
# October 26th, 2010
#
# Targets:	a2 -- executable
#
#		clean -- removes .o files and executable

OBJ1 = a2.o
CC = g++

a2: $(OBJ1)
	$(CC) $(OBJ1) -o a2

a2.o: a2.cpp 
	$(CC) -c -g a2.cpp

clean: 
	rm -f *.o a2
