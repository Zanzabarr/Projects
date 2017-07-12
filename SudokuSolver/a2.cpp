/********************************************************************
* Csci 485AI, Assignment #2
*********************************************************************
* File:
*     a2.cpp
*
* Purpose:
*     9x9 Sudoku puzzle solver. Considers the problem as a CSP, and uses
*     forward checking and variable ordering heuristics to solve the
*     problem more efficiently.
*
* Notes:
*     None
*
* Date:
*     October 14th, 2010
*
* Author:
*     Ryan Osler
*********************************************************************/

/* Includes */
#include <iostream>
#include <string>
#include <math.h>

using namespace std;

/* Constants */
const int DIMENSION = 9;

/* Structs */
struct Variable {
    char value;
    int domainSize;
    bool *domain;
};

struct domainNode {
    int row, col, domainSize;
    bool *domain;
    domainNode *next;
};

/* Function Prototypes */
void initBoard(Variable **, int &);
void initDomains(Variable **);
void displayBoard(Variable **);
void boolArrCpy(bool *, bool *, int);
domainNode * forwardCheck(Variable **, char, int, int);
bool backtrackSearch(Variable **, int);
bool sameBox(int, int, int, int);
int numConstraints(Variable **, int, int);

static int backtrackCount = 0;


int main() 
{
    // create empty DIMENSION X DIMENSION sudoku board
    Variable **sudokuBoard = new Variable*[DIMENSION];
    for(int i = 0; i < DIMENSION; i++) {
        sudokuBoard[i] = new Variable[DIMENSION];
    }

    int numAVars = 0; // number of assigned variables

    // initialize board using standard input values.
    initBoard(sudokuBoard, numAVars);

    // initial domain pruning for initial board state
    initDomains(sudokuBoard);
 
    if(backtrackSearch(sudokuBoard, numAVars)) {
        cout << "\nSudoku Puzzle Solved with only " << backtrackCount
             << " backtracks!\n\n";    
        displayBoard(sudokuBoard);  
        cout << endl; 
    }
    else {
        cout << "\nNo Solution for this Sudoku Puzzle Exists\n\n";
    }    

    for(int i = 0; i < DIMENSION; i++) {
        delete(sudokuBoard[i]);
    }   

    cout << endl;

    return 0;    
}


void initBoard(Variable **board, int &numAVars) 
{
    // Initialize board from standard input.
    // Expects DIMENSION number of lines, each with
    // DIMENSION number of characters, from 1-DIMENSION or 
    // underscore to represent an empty square.
    // Assumes input format is valid.

    cout << "Please Enter State of " << DIMENSION << " by " << DIMENSION
         << " Sudoku Puzzle.\n\nEnter " << DIMENSION << " lines of "
         << DIMENSION << " characters each, of values 1 through "
         << DIMENSION << ",\nor underscore for empty square." << endl;

    string readValues;

    for(int i = 0; i < DIMENSION; i++) {
        getline(cin, readValues);
        for(int j = 0; j < DIMENSION; j++) {
            board[i][j].value = readValues[j];
            if(board[i][j].value != '_') {
                numAVars++;
                board[i][j].domainSize = 0;
                board[i][j].domain = NULL;
            }
            else {
                board[i][j].domainSize = DIMENSION;
                board[i][j].domain = new bool[DIMENSION];
                for(int k = 0; k < DIMENSION; k++) {
                    board[i][j].domain[k] = true;
                }    
            }
        }
    }    
}


void displayBoard(Variable **board) 
{
    // Outputs DIMENSION X DIMENSION board.
    // underscore represents an empty square.

    for(int i = 0; i < DIMENSION; i++) {
        for(int t = 0; t < DIMENSION; t++) {
            if(i % 3 == 0) {
                cout << " ~~~"; 
            }
            else {
                cout << " ---";
            }
        }
        cout << "\n|";
        for(int j = 0; j < DIMENSION; j++) {
            cout << " " << board[i][j].value;
            if(j % 3 == 2) {
                cout << " |";
            } 
            else {
                cout << " :";   
            }
        }
        cout << endl;
    }
    for(int t = 0; t < DIMENSION; t++) {
        cout << " ~~~"; 
    }
}


void initDomains(Variable **board)
{
    for(int i = 0; i < DIMENSION; i++) {
        for(int j = 0; j < DIMENSION; j++) {
            if(board[i][j].value != '_') {
                forwardCheck(board, board[i][j].value, i, j);
            }    
        }
    }
}


bool sameBox(int r1, int c1, int r2, int c2) 
{
    // returns true if (r1,c1) is in same 3x3 box
    // as (r2,c2) on sudoku board

    return((int)ceil((r1+1)/3.0) == (int)ceil((r2+1)/3.0) && (int)ceil((c1+1)/3.0) == (int)ceil((c2+1)/3.0));
}


int numConstraints(Variable **board, int r, int c) 
{
    // returns the number of other unassigned variables that are involved in a
    // constraint with passed in var (where var has at least one value in its domain that is
    // also in the unassigned variable's domain). (passed in var represented through row/col
    // coord on board.

    int numC = 0;

    for(int i = 0; i < DIMENSION; i++) {
        for(int j = 0; j < DIMENSION; j++) {
            if(i == r || j == c || sameBox(i, j, r, c)) {
                if(board[i][j].value == '_') {
                    // unassigned variable is involved in a constraint.
                    // see if it has at least 1 domain value in common
                    for(int k = 0; k < DIMENSION; k++) {
                        if(board[i][j].domain[k] && board[r][c].domain[k]) {
                            numC++;
                            break;
                        }    
                    }
                }
            }
        }
    } 

    return numC;         
}


void boolArrCpy(bool *arr1, bool *arr2, int size) 
{
    // copy the contents of arr2 into arr1

    for(int i = 0; i < size; i++) {
        arr1[i] = arr2[i];
    }
}


domainNode * forwardCheck(Variable **board, char newVal, int r, int c)
{
// For each unassigned variable, cycle through every one of its domain values and
// remove the domain value previously assigned from its domain if it is involved in
// a constraint with the assigned variable.

// fc row, col, and 3x3 box that assigned variable is in
    int domainLoc = (((int)(newVal - '0')) - 1); // loc of domain to remove in each
                                                 // domain array of an unassigned
                                                 // variable involved in a constraint

    domainNode *modDomainList = NULL;
    domainNode *prev;               

    for(int i = 0; i < DIMENSION; i++) {
        for(int j = 0; j < DIMENSION; j++) {
            if(i == r || j == c || sameBox(i, j, r, c)) {
                if(board[i][j].value == '_') {
                    // empty square. 
                    // remove assigned value from domain if it is in domain
                    if(board[i][j].domain[domainLoc]) {
                        // create backups
                        domainNode *temp = new domainNode;
                        temp->row = i;
                        temp->col = j;
                        temp->domainSize = board[i][j].domainSize;
                        temp->domain = new bool[DIMENSION];
                        boolArrCpy(temp->domain, board[i][j].domain, DIMENSION);
                        temp->next = NULL;

                        if(modDomainList == NULL) {
                            modDomainList = temp;
                        } 
                        else {
                            prev->next = temp;
                        }

                        prev = temp;

                        // remove from domain
                        board[i][j].domain[domainLoc] = false;
                        board[i][j].domainSize--; 
                    }        
                }
            }
        }
    } 

    return modDomainList;  
}


bool backtrackSearch(Variable **board, int numAVars)
{
    if(numAVars == (DIMENSION * DIMENSION)) {
        return true;
    }

    Variable *varToAssign = NULL;
    int vRow, vCol, numC;

    // choose Variable to assign using Variable Ordering Heuristic
    // (Variable with smallest domain, or minimum remaining values)
    // Tie-breaker is most constraining variable (the one involved in
    // the most constraints of unassigned variables).
    for(int i = 0; i < DIMENSION; i++) {
        for(int j = 0; j < DIMENSION; j++) {
            if(board[i][j].value == '_') {
                // Unassigned variable
                if(!varToAssign || board[i][j].domainSize < varToAssign->domainSize
                    || (board[i][j].domainSize == varToAssign->domainSize && 
                        numConstraints(board, i, j) > numC)) {

                    varToAssign = &(board[i][j]);
                    vRow = i;
                    vCol = j;
                    numC = numConstraints(board, vRow, vCol);
                }    
            }      
        }
    } 
    
    if(!varToAssign) {
        // no variables to assign left. Complete consistent assignment
        // because of the assigned variable check at top, shoulder never
        // happen
        return true;
    }

    // store back-up information
    int origDomainSize = varToAssign->domainSize;
    bool *origDomain = varToAssign->domain;
    domainNode *modVarDomainsList = NULL;

    // choose value to assign to given variable
    for(int k = 0, l = 0; k < DIMENSION && l < origDomainSize; k++) {
        if(varToAssign->domain[k]) {
            l++;
            varToAssign->value = '0' + (k + 1);
            varToAssign->domainSize = 0;            
            varToAssign->domain = NULL; 

            modVarDomainsList = forwardCheck(board, varToAssign->value, vRow, vCol);
           
            if(backtrackSearch(board, (numAVars + 1))) {
                delete(origDomain); 
                return true;
            }
            else {
                // restore domains of Variables that were modified by
                // forward checking in preparation for the next iteration.
                varToAssign->domainSize = origDomainSize;
                varToAssign->domain = origDomain;

                while(modVarDomainsList != NULL) {
                    int r = modVarDomainsList->row;
                    int c = modVarDomainsList->col;

                    boolArrCpy(board[r][c].domain, modVarDomainsList->domain, DIMENSION);
                    board[r][c].domainSize = modVarDomainsList->domainSize;

                    domainNode *temp = modVarDomainsList; 
                    modVarDomainsList = modVarDomainsList->next;
                    delete(temp);
                } 
            } 
        }        
    }
     
    // no valid values can be assigned to chosen variable.
    // forced to backtrack. Restore old values.
    backtrackCount++; 
    varToAssign->value = '_';
    varToAssign->domainSize = origDomainSize;
    varToAssign->domain = origDomain;

    return false;
}
