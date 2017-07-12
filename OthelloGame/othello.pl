 %********************************************************************
 %% Csci 330, Assignment #4
 %%********************************************************************
 %% File:
 %%     othello.pl
 %%
 %% Purpose:
 %%     To create a an othello game in prolog. Player is black, computer
 %%     is white. Black moves first. 
 %%
 %% Notes:
 %%     Pieces on the board are stored in a list with coordinates and colour
 %%     for each list element. Valid moves for each player are calculated
 %%     using this list before each move. Liberal use of append is needed
 %%     to make the way I coded this work. (May not be the most efficient
 %%     way to do this, but it was the way I thought of doing it, and
 %%     was pretty much committed to it).
 %%
 %% Date:
 %%     March 29, 2010
 %%
 %% Author:
 %%     Ryan Osler
 %%********************************************************************

/* Displays Othello Board. LP is list of pieces in play [[Row,Col,Colour],...] */
/* LVB is the list of valid moves for black [[Row,Col],...] */
displayBoard(LP,LVB):- write('The current board is:'), nl,
	               write(' '), write(' '), 
                       displayTop(8,1), 
                       displayBoardA(LP,LVB,8,8,1,1).

/* Helper function for displayBoard. Displays numbers at top of board */
displayTop(C,C1):- C1 > C, nl, !.
displayTop(C,C1):- write(' '), write(C1), C2 is C1 + 1,
                   displayTop(C,C2).

/* Helper function for displayBoard. Displays each row using displayRow. */
/* LP is list of pieces in play, LVB is list of valid moves for black. */
/* R and C are Row/Column max, R1 and C1 are Row/Column current */
displayBoardA(_,_,R,_,R1,_):- R1 > R, !.
displayBoardA(LP,LVB,R,C,R1,C1):- displayRow(LP,LVB,LPOUT,LVBOUT,R1,C,C1),
                                  R2 is R1 + 1,
				  displayBoardA(LPOUT,LVBOUT,R,C,R2,C1).

/* Displays rows of board. LP is list of pieces in play. LVB is list of valid moves for black */
/* LPOUT and LVBOUT are the modified LP/LVB (once a piece in one of these lists has been displayed */
/* it is removed from the list for efficiency */
displayRow(LP,LVB,LPOUT,LVBOUT,R1,C,C1):- write(R1), write(' '),
                                          displayRowA(LP,LVB,LPOUT,LVBOUT,R1,C,C1).

/* Helper function for displayRow. Displays either a colour, a *, or blank for each column */
/* in the row depending on whether or not the coord is in LP or LVB */
displayRowA(LP,LVB,LP,LVB,_,C,C1):- C1 > C, write('|'), nl, !.
displayRowA(LP,LVB,LP,LVB,R1,C,C1):- append(L,[[R1,C1,COLOUR]|L1],LP), !,
                                     write('|'), write(COLOUR),
			             C2 is C1 + 1,
				     append(L,L1,LP1),
				     displayRowA(LP1,LVB,LP1,LVB,R1,C,C2).

displayRowA(LP,LVB,LP,LVB,R1,C,C1):- append(L,[[R1,C1]|L1],LVB), !,
                                     write('|'), write('*'),
			             C2 is C1 + 1,
			             append(L,L1,LVB1),
		       		     displayRowA(LP,LVB1,LP,LVB1,R1,C,C2).

displayRowA(LP,LVB,LP,LVB,R1,C,C1):- write('|'), write(' '),
			             C2 is C1 + 1,
				     displayRowA(LP,LVB,LP,LVB,R1,C,C2).

/* Starts main execution of Othello Game */
run:- write('Welcome to Othello. You will be playing the black pieces (marked b).'), nl,
      write('I will be playing the white pieces (marked w). The spaces marked with an *'), nl,
      write('indicate places where it would be valid for you to place one of your pieces.'), nl,
      write('You will go first.'), nl, nl,
      initGame.

/* Initializes pieces in play to be the 4 standard pieces, with a current piece count of 4 */
initGame:- play([[4,4,w],[4,5,b],[5,4,b],[5,5,w]], 4).

/* Plays the game. Ends game when piece count is 64 (board full), or neither player can move */
/* (board all one colour), and declares winner with stats */
/* LP is list of pieces in play, PCOUNT is piece count */
play(LP, PCOUNT):- PCOUNT = 64, !, endGame(LP).
play(LP, PCOUNT):- calcValidMoves(LP,b,LVB), !,
                   displayBoard(LP,LVB), nl, 
                   countEach(LP,0,0,NUMBLACKOUT,NUMWHITEOUT),
                   write('Black: '), write(NUMBLACKOUT), nl,
                   write('White: '), write(NUMWHITEOUT), nl, nl,
                   playerTurn(LP,LPOUT,LVB,PCOUNT,PCOUNTOUT), !,
		   (PCOUNTOUT = 64, !, endGame(LPOUT)
                    ;calcValidMoves(LPOUT,w,LVW), !,
                     computerTurn(LPOUT,LPOUT2,LVW,PCOUNTOUT,PCOUNTOUT2), !,
                     nl,
		     (PCOUNTOUT2 = PCOUNT, !, endGame(LPOUT2)
                      ;play(LPOUT2, PCOUNTOUT2))).

/* End of game, display final state of board, and calculate number of pieces for each colour */
/* Tells player he either won, lost, or draw, and outputs piece count for each player */
endGame(LP):- displayBoard(LP,[]), nl,
              countEach(LP,0,0,NUMBLACKOUT,NUMWHITEOUT),
              write('Final Score:'), nl, nl, write('Black: '), write(NUMBLACKOUT), nl,
	      write('White: '), write(NUMWHITEOUT), nl, nl,
	      (NUMBLACKOUT > NUMWHITEOUT, !, write('You are Victorious... surely a miracle')
	       ;NUMWHITEOUT > NUMBLACKOUT, !, write('You are Defeated... mere mortals are no match for me!')
	       ;write('A Draw..... most unfavourable')).

/* Counts number of pieces for black and white */
/* Takes in list of current pieces, and starting counts for b and w, and as side effect */
/* sets b and w count out to be final piece counts */
countEach([[_,_,b]|LP],BIN,WIN,BOUT,WOUT):- BIN1 is BIN + 1, countEach(LP,BIN1,WIN,BOUT,WOUT).  
countEach([[_,_,w]|LP],BIN,WIN,BOUT,WOUT):- WIN1 is WIN + 1, countEach(LP,BIN,WIN1,BOUT,WOUT). 
countEach([],BIN,WIN,BIN,WIN).

/* Players turn. LP is list of pieces in play, LVB is list of valid moves for black (player) */
/* CTIN is current piece count on board. If no valid moves available, forces player to pass */
/* As side effect, sets the list of pieces out to the modified list after a move was made, and */
/* sets CTOUT to the Count of pieces now on the board (unchanged if pass, +1 if move was made */
playerTurn(LP,LP,[],CTIN,CTIN):- !, write('You are forced to pass'), nl. 
playerTurn(LP,LPOUT,LVB,CTIN,CTOUT):- CTOUT is CTIN + 1, playerTurnA(LP,LPOUT,LVB).

/* Helper function for playerTurn. Gets player turn coord from user input */
playerTurnA(LP,LPOUT,LVB):- write('Please enter the row and column where you would like to place your piece.'),
                            nl,
	  	            skip, get0(ROW), skip, get0(COL), flush_input,
	 	            (ROW >= 49, ROW =< 56, COL >= 49, COL =< 56,
		             ROW1 is ROW - 48, COL1 is COL - 48,
                             append(_,[[ROW1,COL1]|_],LVB), !,
		             recalcBoard(LP,LP,[ROW1,COL1,b],LPOUT)
			     ;playerTurnError(LP,LPOUT,LVB)).

/* Displays user error if turn input was invalid. */
playerTurnError(LP,LPOUT,LVB):- write('That position is not valid, Remember that you must pick a spot that has a * '),
                                nl, write('at its location.'), nl, nl,
		                playerTurnA(LP,LPOUT,LVB).

/* Computers Turn. LP is list of pieces in play, LVW is list of valid moves for white (computer) */
/* CTIN is piece count in. As side effect, LPOUT is list of pieces after computer turn was made, and */
/* CTOUT is piece count after turn (unchanged if pass, +1 if move was made) */
computerTurn(LP,LP,[],CTIN,CTIN):- !, write('I am forced to pass'), nl.
computerTurn(LP,LPOUT,LVW,CTIN,CTOUT):- CTOUT is CTIN + 1, computerTurnA(LP,LPOUT,LVW).

/* Helper function for computerTurn. chooses location from list of valid moves based on a set of */
/* axioms involving the stats of the location (corner, side, etc) or if its next to one of those and so on */
/* Also considers how good the next turn of the opponent is after a move */
computerTurnA(LP,LPOUT,LVW):- sortByWeight(LVW,LP,LVWM), !,
                              chooseLocation(LP,LVWM,ROW,COL), !, 
                              write('I have chosen location '), write(ROW), write(' '), write(COL), nl,
                              recalcBoard(LP,LP,[ROW,COL,w],LPOUT).

/* sorts given list of valid moves by weight (lowest to highest) */
/* lower weight means less good location choices for the opponent on his next turn */
sortByWeight(LVW,LP,WLOUT):- getWeights(LVW,LP,WL), quicksort(WL,WLOUT).

quicksort([[R,C,T]|Xs],Ys):- partition(Xs,[R,C,T],Left,Right),
                             quicksort(Left,Ls),
                             quicksort(Right,Rs),
                             append(Ls,[[R,C,T]|Rs],Ys).
                             quicksort([],[]).

/* used with quicksort */
partition([[R,C,T]|Xs],[R1,C1,T1],[[R,C,T]|Ls],Rs) :- T =< T1, partition(Xs,[R1,C1,T1],Ls,Rs).
partition([[R,C,T]|Xs],[R1,C1,T1],Ls,[[R,C,T]|Rs]) :- T > T1, partition(Xs,[R1,C1,T1],Ls,Rs).
partition([],[_,_,_],[],[]).

/* gets the weights of all of the valid moves, and stores them along with the coord of the move */
getWeights([],_,[]):- !.
getWeights([[ROW,COL]|LVW],LP,[[ROW,COL,TOUT]|WL]):- getWeightsA([ROW,COL,w],LP,TOUT), !, getWeights(LVW,LP,WL).

/* Helper for getWeights. Recalculates copy of board after a valid move, calculates the list */
/* of valid moves the opponent would have, and then calculates the sum "value" of opponents moves */
/* based on different factors, and returns this sum as side effect. (Becomes weight of valid move)*/
getWeightsA([ROW,COL,COLOUR],LP,TOUT):- recalcBoard(LP,LP,[ROW,COL,COLOUR],LPOUT), !,
                                        calcValidMoves(LPOUT,b,LVOPP), !,
                                        rateMoves(LPOUT,LVOPP,0,TOUT).

/* mechanism for rating an opponents moves. Sums the values of the moves the opponent would have */
/* High weights are attached to moves that are good for the opponent. */ 
rateMoves(_,[],TIN,TIN):- !.
rateMoves(LP,[[R,C]|LVOPP],TIN,TOUT):- (corner(R,C), TIN1 is TIN + 25000
                                       ;side(R,C), nextToCorner([R,C],[R1,C1]), 
                                        append(_,[[R1,C1,b]|_],LP), TIN1 is TIN + 3000
                                       ;side(R,C), X=..[nextToCorner,[R,C],[_,_]], not(X), TIN1 is TIN + 1500
                                       ;Y=..[nextToCorner,[R,C],[_,_]], not(Y), 
                                        Z=..[nextToSide,[R,C],[_,_]], not(Z), TIN1 is TIN + 200
                                       ;A=..[nextToCorner,[R,C],[_,_]], not(A), TIN1 is TIN + 30
                                       ;side(R,C), TIN1 is TIN + 2
                                       ;nextToCorner([R,C],[R1,C1]), append(_,[[R1,C1,w]|_],LP), TIN1 is TIN + 15
                                       ;TIN1 is TIN + 1), !, rateMoves(LP,LVOPP,TIN1,TOUT).


/* The computer logic for selecting a position from the list of valid moves */
/* The list of valid moves passed in has been sorted in order of weight(ascending), with the weight of
 * each move included. A lesser weight means more minimized the next opponents move is, and is therefore
 * a better move. Each move is tried for an axiom, lowest to highest weight. If no moves are valid for
 * an axiom in the priority list, the next lower axiom is tried. The first move that is accepted is used.
 *
 * Priority List: 
 * 1) Move that will force opponent to pass
 * 2) corner location
 * 3) side location adjacent to a corner occupied by own piece
 * 4) side location not adjacent to a corner
 * 5) location not adjacent to a corner and not adjacent to a side
 * 6) location not adjacent to a corner
 * 7) side location.
 * 8) any location.
*/
chooseLocation(_,[[ROW,COL,WT]|_],ROW,COL):- WT = 0, !.
chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), corner(ROW,COL), !.
chooseLocation(LP,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), side(ROW,COL), nextToCorner([ROW,COL],[R1,C1]),
                                                                               append(_,[[R1,C1,w]|_],LP), !.

chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), side(ROW,COL), Y=..[nextToCorner,[ROW,COL],[_,_]], not(Y), 
!.
chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), X=..[nextToCorner,[ROW,COL],[_,_]], not(X),
                                                               Y=..[nextToSide,[ROW,COL],[_,_]], not(Y), !.

chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), X=..[nextToCorner,[ROW,COL],[_,_]], not(X), !.
chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW), side(ROW,COL), !.
chooseLocation(_,LVW,ROW,COL):- append(_,[[ROW,COL,_]|_],LVW).

/* Locations of corners on the board */
corner(1,1).
corner(1,8).
corner(8,1).
corner(8,8).

/* Locations of sides on the board */
side(1,C):- C >= 2, C =< 7.
side(8,C):- C >= 2, C =< 7.
side(R,1):- R >= 2, R =< 7.
side(R,8):- R >= 2, R =< 7.

/* True if Location is adjacent to a corner location on board */
nextToCorner([R,C],[R1,C1]):- adjacent([R,C],[R1,C1]), corner(R1,C1).

/* True if Location is adjacent to a side location on board */
nextToSide([R,C],[R1,C1]):- adjacent([R,C],[R1,C1]), side(R1,C1).

/* Calculates the list of valid moves for the colour of the player given */
/* LP is list of current pieces on board, COLOUR is colour of player, LV */
/* becomes the list of valid moves for given COLOUR as side effect */
calcValidMoves(LP,COLOUR,LV):- calcValidMovesA(LP,COLOUR,[],LV,1,1).

/* Helper for calcValidMoves */
calcValidMovesA(_,_,LV,LV,R,_):- R > 8, !.
calcValidMovesA(LP,COLOUR,LV,LV1,R,C):- calcValidMovesRow(LP,COLOUR,LV,LVOUT,R,C), !,
                                        R1 is R + 1,
			                calcValidMovesA(LP,COLOUR,LVOUT,LV1,R1,C).

/* Calculates the valid moves for a row on the board */
calcValidMovesRow(_,_,LV,LV,_,C):- C > 8, !.
calcValidMovesRow(LP,COLOUR,LV,LVOUT,R,C):- X=..[append,_,[[R,C,_]|_],LP], not(X),
                                            adjacent([R,C],[R1,C1]),
                                            append(_,[[R1,C1,COLOUR1]|_],LP),
				            COLOUR1 \= COLOUR,
				            inline(LP,[R,C],[R1,C1],[R2,C2]),
				            append(_,[[R2,C2,COLOUR]|_],LP), !,
				            C3 is C + 1,
                                            calcValidMovesRow(LP,COLOUR,[[R,C]|LV],LVOUT,R,C3).

calcValidMovesRow(LP,COLOUR,LV,LVOUT,R,C):- C1 is C + 1, calcValidMovesRow(LP,COLOUR,LV,LVOUT,R,C1).

/* Reforms board based on given move to be played */
/* Takes in List of pieces on board twice (one to iterate through, one to keep as reference */
/* as well as the move to be played. LPOUT becomes list of current pieces on board after recalc */
recalcBoard([[R,C,COLOUR]|_],LPF,[R1,C1,COLOUR1],LPOUT):- COLOUR \= COLOUR1, 
                                                          adjacent([R,C],[R1,C1]),
                                                          inline(LPF,[R1,C1],[R,C],[R2,C2]),
			  			          append(_,[[R2,C2,COLOUR1]|_],LPF), !,
                                                          flip(LPF,LPF,LPOUT1,[R1,C1],[R2,C2],COLOUR1),
						          recalcBoard(LPOUT1,LPOUT1,[R1,C1,COLOUR1],LPOUT).
							    
recalcBoard([[_,_,_]|LP],LPF,MOVE,LPOUT):- recalcBoard(LP,LPF,MOVE,LPOUT).
recalcBoard([],LPOUT,MOVE,[MOVE|LPOUT]).

/* Performs the actual "flipping" of the pieces during recalculation */
/* Takes in the list of pieces (twice, one for iteration), and coords for the two anchors */
/* to flip between (All pieces between these anchors in straight line will be flipped. */
/* FLIPCOLOUR is the colour to flip the pieces to. */
flip([[R,C,COLOUR]|_],LPF,LPOUT,[R1,C1],[R2,C2],FLIPCOLOUR):- COLOUR \= FLIPCOLOUR, 
                                                              between(LPF,[R1,C1],[R2,C2],[R,C]),
							      append(L,[[R,C,COLOUR]|L1],LPF), !,
							      append(L,[[R,C,FLIPCOLOUR]|L1],LPOUT1),
							      flip(LPOUT1,LPOUT1,LPOUT,[R1,C1],[R2,C2],FLIPCOLOUR).
							 
flip([[_,_,_]|LP],LPF,LPOUT,P1,P2,FLIPCOLOUR):- flip(LP,LPF,LPOUT,P1,P2,FLIPCOLOUR).
flip([],LPOUT,LPOUT,_,_,_).

/* True if 3rd location is located on the straight line of locations on the board in the same direction */ 
/* as the line produced from the 1st location to the 2nd location given, and there are no empty locations */
/* along the line in getting to the 3rd location. Takes in a list of pieces on the board, location 1, location 2 */
/* Returns location 3 as side effect if used that way(to recurse through all possible inline locations on board), */
/* or can make sure that location 3 given is indeed "inline" with location 1,2. */
inline(LP,[R,C],[R,C1],[R,C2]):- C1 > C, toRight(LP,R,C1,C2).
inline(LP,[R,C],[R,C1],[R,C2]):- C1 < C, toLeft(LP,R,C1,C2).
inline(LP,[R,C],[R1,C],[R2,C]):- R1 > R, toBelow(LP,R1,R2,C).
inline(LP,[R,C],[R1,C],[R2,C]):- R1 < R, toAbove(LP,R1,R2,C).
inline(LP,[R,C],[R1,C1],[R2,C2]):- R1 > R, C1 > C, toBelowRight(LP,[R1,C1],[R2,C2]).
inline(LP,[R,C],[R1,C1],[R2,C2]):- R1 > R, C1 < C, toBelowLeft(LP,[R1,C1],[R2,C2]).
inline(LP,[R,C],[R1,C1],[R2,C2]):- R1 < R, C1 > C, toAboveRight(LP,[R1,C1],[R2,C2]).
inline(LP,[R,C],[R1,C1],[R2,C2]):- R1 < R, C1 < C, toAboveLeft(LP,[R1,C1],[R2,C2]).

toRight(LP,R,C1,C2):- C2 is C1 + 1, C2 =< 8, (Y=..[append,_,[[R,C2,_]|_],LP], not(Y), !, fail ; true).
toRight(LP,R,C1,C2):- C3 is C1 + 1, C3 < 8, toRight(LP,R,C3,C2).

toLeft(LP,R,C1,C2):- C2 is C1 - 1, C2 >= 1, (Y=..[append,_,[[R,C2,_]|_],LP], not(Y), !, fail ; true).
toLeft(LP,R,C1,C2):- C3 is C1 - 1, C3 > 1, toLeft(LP,R,C3,C2).

toBelow(LP,R1,R2,C):- R2 is R1 + 1, R2 =< 8, (Y=..[append,_,[[R2,C,_]|_],LP], not(Y), !, fail ; true).
toBelow(LP,R1,R2,C):- R3 is R1 + 1, R3 < 8, toBelow(LP,R3,R2,C).

toAbove(LP,R1,R2,C):- R2 is R1 - 1, R2 >= 1, (Y=..[append,_,[[R2,C,_]|_],LP], not(Y), !, fail ; true).
toAbove(LP,R1,R2,C):- R3 is R1 - 1, R3 > 1, toAbove(LP,R3,R2,C).

toBelowRight(LP,[R1,C1],[R2,C2]):- R2 is R1 + 1, R1 =< 8, C2 is C1 + 1, C2 =< 8, (Y=..[append,_,[[R2,C2,_]|_],LP], not(Y), !, fail ; true).
toBelowRight(LP,[R1,C1],[R2,C2]):- R3 is R1 + 1, R3 < 8, C3 is C1 + 1, C3 < 8, toBelowRight(LP,[R3,C3],[R2,C2]).

toBelowLeft(LP,[R1,C1],[R2,C2]):- R2 is R1 + 1, R1 =< 8, C2 is C1 - 1, C2 >= 1, (Y=..[append,_,[[R2,C2,_]|_],LP], not(Y), !, fail ; true).
toBelowLeft(LP,[R1,C1],[R2,C2]):- R3 is R1 + 1, R3 < 8, C3 is C1 - 1, C3 > 1, toBelowLeft(LP,[R3,C3],[R2,C2]).

toAboveRight(LP,[R1,C1],[R2,C2]):- R2 is R1 - 1, R1 >= 1, C2 is C1 + 1, C2 =< 8, (Y=..[append,_,[[R2,C2,_]|_],LP], not(Y), !, fail ; true).
toAboveRight(LP,[R1,C1],[R2,C2]):- R3 is R1 - 1, R3 > 1, C3 is C1 + 1, C3 < 8, toAboveRight(LP,[R3,C3],[R2,C2]).

toAboveLeft(LP,[R1,C1],[R2,C2]):- R2 is R1 - 1, R1 >= 1, C2 is C1 - 1, C2 >= 1, (Y=..[append,_,[[R2,C2,_]|_],LP], not(Y), !, fail ; true).
toAboveLeft(LP,[R1,C1],[R2,C2]):- R3 is R1 - 1, R3 > 1, C3 is C1 - 1, C3 > 1, toAboveLeft(LP,[R3,C3],[R2,C2]).

/* Returns true if location 3 is between location 1 and 2 */
/* Takes in a list of pieces on the board, and 3 locations as parameters */
/* If location 3 is not given, will set it to a valid location between the first two locations as side effect */
between(LP,[R,C],[R1,C1],[R2,C2]):- inline(LP,[R,C],[R2,C2],[R1,C1]).

/* Returns true if location 1 is adjacent to location 2 */
/* If used without 2nd location, sets that location to a valid adjacent location as side effect */
/* (used to recurse through all possible adjacent locations to a location in at least one instance of another function */
adjacent([R1,C1],[R1,C2]):- C2 is C1 - 1, C2 >= 1.
adjacent([R1,C1],[R1,C2]):- C2 is C1 + 1, C2 =< 8.
adjacent([R1,C1],[R2,C1]):- R2 is R1 - 1, R2 >= 1.
adjacent([R1,C1],[R2,C1]):- R2 is R1 + 1, R2 =< 8.
adjacent([R1,C1],[R2,C2]):- R2 is R1 - 1, C2 is C1 - 1, R2 >= 1, C2 >= 1.
adjacent([R1,C1],[R2,C2]):- R2 is R1 - 1, C2 is C1 + 1, R2 >= 1, C2 =< 8.
adjacent([R1,C1],[R2,C2]):- R2 is R1 + 1, C2 is C1 - 1, R2 =< 8, C2 >= 1.
adjacent([R1,C1],[R2,C2]):- R2 is R1 + 1, C2 is C1 + 1, R2 =< 8, C2 =< 8.

/* flush input stream up to and including newline char */
flush_input:- repeat, get0(C), C = 10.

/* skip all whitespace on input stream */
skip:- peek_code(C), C = 32, get0(_), skip.
skip.

/* returns true if parameter called is false. Returns false if parameter called is true */
not(X):- call(X), !, fail.
not(_).
