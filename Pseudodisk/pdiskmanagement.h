/********************************************************************
* Csci 360 Lab #4
*********************************************************************
* File:
*     pdiskmanagement.h
*
* Purpose:
*     Contains the prototypes for the functions that both create and
*     operate the pseudodisk (of which simulates an actual disk), as
*     well as any #defines or typesdefs used. The pseudodisk itself is a
*     file, whose name and size is defined below.
*
* Notes:
*     There is no Master Boot Record entry taken into account for the
*     pseudodisk, nor is there a partition table. The pseudodisk
*     expects to have at most 1 file system on it at a time.
*
* Date:
*     November 18, 2009
*
* Author:
*     Ryan Osler
********************************************************************/

/* Includes */
#include <stdio.h>

/* Defines */

/* All sizes in bytes */
#define DISK_BLOCK_SIZE 64
#define DISK_SIZE (DISK_BLOCK_SIZE * 256)

#define DISK_NAME "pdisk.disk" 

typedef unsigned char Diskblock[DISK_BLOCK_SIZE]; 

/* Globals */

/* Used by pseudodisk operations to read, write and seek
   the pseudodisk.
*/ 
FILE *globalDiskFD; 

/* Function Prototypes */

/* Creates pseudodisk file of name DISK_NAME, and initializes 
   its size to DISK_SIZE bytes, all initially 0.
   Returns 1 on success, and 0 if the disk file already 
   exists, or if the operation otherwise failed.
*/
int diskCreate(); 

/* Opens the pseudodisk file of name DISK_NAME for reading and
   writing. 
   Returns 1 on success, 0 on failure.
*/
int diskOpen(); 

/* Closes the pseudodisk file. 
   Returns 1 on success, 0 on failure.
*/
int diskClose();

/* Properly saves all data written to the pseudodisk since 
   it was opened. This function is required, as the pseudodisk
   is itself a file, and hence needs to be closed and then
   reopened to properly update changes made to it (in order
   for any reads of recently written data to work). 
   Returns 1 on success, 0 on failure.
*/
int diskSave();

/* Reads a disk block of size DISK_BLOCK_SIZE from the current
   position of the head in the pseudodisk file, and stores it 
   in the "Diskblock" array passed in.
   Returns 1 on success, 0 on failure.
*/
int diskRead(Diskblock blockRead);

/* Writes a disk block, "blockToWrite" of size DISK_BLOCK_SIZE 
   to the pseudodisk file at the current location of the head.
   Returns 1 on success, 0 on failure.
*/
int diskWrite(Diskblock blockToWrite);

/* Seeks the head of the pseudodisk to the disk block number 
   specified by "blockNum".
   Returns 1 on success, 0 on failure.
*/ 
int diskSeek(int blockNum);

/* Resets the pseudodisk file of name DISK_NAME and of size 
   DISK_SIZE bytes to all 0's. 
   Returns 1 on success, and 0 if the disk file does not 
   exist, or the operation otherwise failed
*/
int diskReformat();
