# Makefile for csci360 Assignment 4
#
# Ryan Osler, November 18, 2009
#
# Targets:
#       main -- make executable for main program
#       clean -- remove all executables, object files, and the pdisk.disk

FLAGS = -lm

OBJ1 = testfilesys.o filesysmanagement.o pdiskmanagement.o

main: $(OBJ1)
	gcc $(OBJ1) -o main $(FLAGS)

testfilesys.o: pdiskmanagement.h filesysmanagement.h testfilesys.c
	gcc -c -g testfilesys.c

filesysmanagement.o: pdiskmanagement.h filesysmanagement.h filesysmanagement.c
	gcc -c -g filesysmanagement.c

pdiskmanagement.o: pdiskmanagement.h pdiskmanagement.c
	gcc -c -g pdiskmanagement.c

clean:
	rm -f *.o main pdisk.disk
