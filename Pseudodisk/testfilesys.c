/********************************************************************
* Csci 360 Lab #4
*********************************************************************
* File:
*     testfilesys.c
*
* Purpose:
*     To test the functionality of the file system defined in
*     "filesysmanagement.h" that is resident on the pseudodisk,
*     whose name and functionality is defined in "pdiskmanagement.h".
*     This program creates the pseudodisk, and then creates and starts
*     up the file system. After doing this, 2 files are created, 
*     written to, read from, closed (and saved to the pseudodisk).
*     After this, the files are reopened again from disk and read
*     from to show proper filesystem and pseudodisk functionality.
*     After these 2 files are closed for a second time,  a new file 
*     is created from the appending of the first 2 files, and read from.              
*
* Notes:
*     This is just a simple test of my pseudodisk and filesystem.
*     More in depth testing of the two parts could have been done,
*     but was not required.
*
* Date:
*     November 18, 2009
*
* Author:
*     Ryan Osler
********************************************************************/

/* Includes */
#include <stdio.h>
#include <string.h>
#include "filesysmanagement.h"

int main() 
{
    int numCharsA, numCharsB, numCharsC, charsWritten, charsRead;
    unsigned short offsetA, offsetB;

    /* text for the 2 files, both larger than 1 disk block */
    char *fileAChars = "Hello World, the world isn't going to end in 2012. Too bad, so sad. Is this string long enough yet?";
    char *fileBChars = "This file system took me forever and a week to code. Now I can finally work on stuff for my other courses!";

    numCharsA = strlen(fileAChars);
    numCharsB = strlen(fileBChars);
    numCharsC = numCharsA + numCharsB;    

    offsetA = 4;
    offsetB = 30;

    /* space to hold chars read from each file (after it is written) */
    char fileACharsRead[numCharsA+1];
    char fileBCharsRead[numCharsB+1];
    char fileCCharsRead[numCharsC+1];

    printf("***Start of the glorious pseudodisk and filesystem testing program***\n\n");
    
    diskCreate();
    fileSysCreate();
    fileSysStartup();

    /* create 2 files in the root directory */
    printf("**Creating files: A,B**\n\n"); 
    fileCreate("ROOT", "A", 'f');
    fileCreate("ROOT", "B", 'f');

    /* open these 2 files */
    printf("**Opening files: A,B**\n\n");
    fileOpen("ROOT", "A");
    fileOpen("ROOT", "B"); 

    /* write to first file */
    printf("**Writing file A**\n");
    charsWritten = fileWrite("A", numCharsA, fileAChars);
    printf("Number of chars successfully written to file A: %d\n\n", charsWritten);
    
    /* write to second file */
    printf("**Writing file B**\n");
    charsWritten = fileWrite("B", numCharsB, fileBChars);
    printf("Number of chars successfully written to file B: %d\n\n", charsWritten);
   
    /* read first file from start of the file */ 
    printf("**Read from file A**\n");
    fileSeek("A", 0); 
    charsRead = fileRead("A", numCharsA, fileACharsRead);
    printf("Number of chars successfully read from file A: %d\n", charsRead);
    printf("Chars read from file A: %s\n\n", fileACharsRead);
    
    /* read second file from start of the file */  
    printf("**Read from file B**\n");
    fileSeek("B", 0);
    charsRead = fileRead("B", numCharsB, fileBCharsRead);
    printf("Number of chars successfully read from file B: %d\n", charsRead);
    printf("Chars read from file B: %s\n\n", fileBCharsRead);
    
    /* read first file from an offset */
    printf("**Read from file A, with offset %d**\n", offsetA);
    fileSeek("A", offsetA); 
    charsRead = fileRead("A", numCharsA, fileACharsRead);
    printf("Number of chars successfully read from file A: %d\n", charsRead);
    printf("Chars read from file A: %s\n\n", fileACharsRead);

    /* read second file from an offset */
    printf("**Read from file B, with offset %d**\n", offsetB);
    fileSeek("B", offsetB);
    charsRead = fileRead("B", numCharsB, fileBCharsRead);
    printf("Number of chars successfully read from file B: %d\n", charsRead);
    printf("Chars read from file B: %s\n\n", fileBCharsRead);

    /* close the files (and save them to the pseudodisk) */
    printf("**Close files: A,B**\n\n");
    fileClose("A");
    fileClose("B");

    /* open the files again, to show that my file system can pull files of multiple
       blocks from the pseudodisk into memory again.
    */
    printf("**Open files: B,A**\n\n");
    fileOpen("ROOT", "B");
    fileOpen("ROOT", "A"); 
    
    /* read first file again */
    printf("**Read from file A**\n");
    charsRead = fileRead("A", numCharsA, fileACharsRead);
    printf("Number of chars successfully read from file A: %d\n", charsRead);
    printf("Chars read from file A: %s\n\n", fileACharsRead);
    
    /* read second file again */
    printf("**Read from file B**\n");
    charsRead = fileRead("B", numCharsB, fileBCharsRead);
    printf("Number of chars successfully read from file B: %d\n", charsRead);
    printf("Chars read from file B: %s\n\n", fileBCharsRead);
    
    /* close files */
    printf("**Close files: A,B**\n\n");
    fileClose("A");
    fileClose("B");

    /* create a third file by appending the second file to the end of the first */
    printf("**Create file: C, by appending B to A**\n\n");
    fileAppend("A", "B", "C");

    /* open this new file */
    printf("**Open file: C**\n\n"); 
    fileOpen("ROOT", "C");

    /* read contents of new file */
    printf("**Read from file: C**\n");
    charsRead = fileRead("C", numCharsC, fileCCharsRead);
    printf("Number of chars successfully read from file C: %d\n", charsRead);
    printf("Chars read from file C: %s\n\n", fileCCharsRead);

    /* shutdown file system */
    fileSysShutdown();

    printf("\n***End of pseudodisk and filesystem Test***\n\n");

    return 0;
}
