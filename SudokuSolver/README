/********************************************************************
* Csci 485AI, Assignment #2
*********************************************************************
* File:
*     README
*
* Purpose:
*     Readme for Assignment #2
*
* Notes:
*     None
*
* Date:
*     October 28th, 2010
*
* Author:
*     Ryan Osler
*********************************************************************/


**************
Program Design
**************
My program uses a dynamically allocated 2D array of Variable structs to
represent the 81 variables of this CSP. Each Variable Struct contains
the value of the variable, and its domain and domain size. 

I initialize the variables through 9 lines of 9 characters from user input,
and assume that the user will have entered this data correctly (characters
1-9 or _ for blank). All assigned variables from this initialization have
a domain of null, and all unassigned variables start with a full domain.
(Domain is represented by an array of bools, representing whether or not
the arraylocation+1 is in the domain. i.e. position 0 represents the value 1, etc.) 
 
I perform an initial Forward check for each initially assigned variable of
the board to prune the domains, and then enter into my backtracking search routine 
to solve the problem. 

The backtracking search routine selects the next variable to assign using variable
ordering (MRV) and a tie-breaker of Most Constraining variable.
Assigns a value to it from its domain, performs a forward check,
and recursively calls itself. Backtracking occurs when a given variable has failed
to successfully solve the problem with each of its current domain values as an assignment.
(which means its attempt to solve the problem with that assignment has backtracked all
the way back to that actual assignment, at which time it tries the next value).

To save space, I merely pass a pointer to my board at every recursive call, and store 
the domains of every variable that had its domains changed for that round of forward
checking. That way, after every backtrack the domains can be restored to enable another
value to be tried. When a variable itself has no more values left to try, it is assigned
as being blank once again before backtracking itself. I believe a lot of space is saved
by not actually saving the entire board of variables and domains at each assignment.

To track the performance of my heuristics, I have included an increment of a static 
global variable for every backtrack that actually occurs in solving a puzzle.


***************
Heuristics Used
***************

I make use of:

1) Forward Checking -- (After the board initialization, and after every
   variable assignment, my forward checking function removes the value
   assigned from the domain of every variable that is involved in a constraint
   with the assigned variable (same row, column, or 3x3 box). For obvious
   reasons, it does not bother trying to remove anything from an already 
   assigned variable (domain for these is null), or an empty square that does
   not have the value in its domain anymore anyway.

2) Variable Ordering -- Every time my program selects a new unassigned variable
   to assign a value to, it goes through the entire list of unassigned variables
   and selects the one with the least remaining values (smallest domain). It tie-breaks
   using Most Constraining Variable.

Using these two heuristics, my program can solve easy sudoku puzzles with 
little to no backtracking at all, and extremely hard sukoku puzzles with
still a backtracking count in the low 100's for the most part. There are some
exceptions however. (Extremely hard puzzles with very very few initial values
can still take 500+ backtracks).

I added the Variable Ordering tie-breaker functionality, and it has seemed to
drastically reduce the number of backtracks needed. Unfortunately, the act
of calculating this at every recursive call probably costs a lot of time.


***************
Running Program
***************

One can easily run my program and solve a puzzle by either piping in a "cat" of
a file for a puzzle in the proper format (9 lines of 9 proper chars) to a call to run
my program: ex: cat puzzle | ./a2 

or through copying and pasting such a format directly into the input stream 
of the running program: ./a2     then copy/paste to command line.

This is much much more convenient than trying in a sudoku puzzle 1 character at a time.
