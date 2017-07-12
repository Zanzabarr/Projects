/********************************************************************
* Csci 360 Lab #4
*********************************************************************
* File:
*     pdiskmanagement.c
*
* Purpose:
*     Contains the implementations for the functions that both create 
*     and operate the pseudodisk (of which simulates an actual disk). 
*     The pseudodisk is itself a file, whose name and size is defined 
*     in "pdiskmanagement.h".
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
#include "pdiskmanagement.h"

/* Function Implementations */
int diskCreate()
{
    FILE *fd;
    size_t size, numElements, writeRet;
    unsigned char diskBytes[DISK_SIZE];
    int i;

    size = sizeof(diskBytes[0]);
    numElements = (size_t)DISK_SIZE;

    for(i = 0; i < DISK_SIZE; i++) {
        diskBytes[i] = (unsigned char)0;
    }

    /* Check to see if pseudodisk file exists. 
       If it already exists, return failure.
    */
    fd = fopen(DISK_NAME, "r");
    if(fd != NULL) {
        fclose(fd);
        return 0;
    }
    
    /* Create and open pseudodisk file for writing.
       Return failure if unable to create and open the file.
    */
    fd = fopen(DISK_NAME, "w");
    if(fd == NULL) {
        return 0;
    }

    /* Initialize the entire pseudodisk to DISK_SIZE many bytes, all 0's.
       Return failure if the pseudodisk was not able to be initialized
       properly.
    */
    writeRet = fwrite(diskBytes, size, numElements, fd);
    if(writeRet != DISK_SIZE) {
        fclose(fd);
        return 0;
    }
    
    /* No errors: close disk and return success */ 
    fclose(fd);
    return 1;
}

int diskOpen()
{
    /* Open pseudodisk file for reading and writing (for operation). 
       Return failure if operation failed or file does not exist.
    */
    globalDiskFD = fopen(DISK_NAME, "r+");
    if(globalDiskFD == NULL) {
        return 0;
    }
    
    /* No errors, return success */
    return 1;
}

int diskClose()
{
    int closeRet;

    /* Close pseudodisk.
       Return failure if operation failed or pseudodisk doesn't exist.
    */
    closeRet = fclose(globalDiskFD);
    if(closeRet != 0){
        return 0;
    }
    
    /* No errors, return success */
    return 1;    
}

int diskSave()
{
    if(!diskClose()) {
        return 0;
    }

    if(!diskOpen()) {
        return 0;
    }

    /* No errors, return success */
    return 1;
}

int diskRead(Diskblock blockRead)
{
    size_t size, numElements, readRet;

    size = sizeof(blockRead[0]);
    numElements = (size_t)DISK_BLOCK_SIZE;

    /* Return failure if pseudodisk is not open */
    if(globalDiskFD == NULL) {
        return 0;
    }

    /* Read a disk block of size "DISK_BLOCK_SIZE" from the current
       position of the head in the pseudodisk, and store it in the
       array pointed to by "blockRead". 
       Return failure if number of bytes read isn't the size of a 
       disk block.
    */ 
    readRet = fread(blockRead, size, numElements, globalDiskFD);
    if(readRet != numElements) {
        return 0;
    }

    /* No errors, return success */
    return 1;
}

int diskWrite(Diskblock blockToWrite)
{
    size_t size, numElements, writeRet;

    size = sizeof(blockToWrite[0]);
    numElements = (size_t)DISK_BLOCK_SIZE;

    /* Return failure if pseudodisk is not open */
    if(globalDiskFD == NULL) {
        return 0;
    }

    /* Write a disk block of size "DISK_BLOCK_SIZE" from the array
       pointed to by "blockToWrite" to the current position of the  
       head in the pseudodisk. 
       Return failure if number of bytes written isn't the size of a 
       disk block.
    */ 
    writeRet = fwrite(blockToWrite, size, numElements, globalDiskFD);
    if(writeRet != numElements) {
        return 0;
    }

    /* No errors, return success */
    return 1;
}

int diskSeek(int blockNum)
{
    int seekRet;
    long offset;

    offset = (long)(blockNum * DISK_BLOCK_SIZE);

    /* Return failure if pseudodisk is not open */
    if(globalDiskFD == NULL) {
        return 0;
    }

    /* Seek to the start of the disk block, "blockNum", in the 
       pseudodisk. 
       Return failure if seek failed.
    */ 
    seekRet = fseek(globalDiskFD, offset, SEEK_SET);
    if(seekRet != 0) {
        return 0;
    }

    /* No errors, return success */
    return 1;
}

int diskReformat()
{
    FILE *fd;
    size_t size, numElements, writeRet;
    unsigned char diskBytes[DISK_SIZE];
    int i;

    size = sizeof(diskBytes[0]);
    numElements = (size_t)DISK_SIZE;

    for(i = 0; i < DISK_SIZE; i++) {
        diskBytes[i] = (unsigned char)0;
    }

    /* Check to see if pseudodisk file exists. 
       If it doesn't exist, return failure.
    */
    fd = fopen(DISK_NAME, "r");
    if(fd == NULL) {
        return 0;
    }
    
    /* Open pseudodisk file for writing.
       Return failure if unable to open the file.
    */
    fd = fopen(DISK_NAME, "w");
    if(fd == NULL) {
        return 0;
    }

    /* Reformat the entire pseudodisk to all 0's.
       Return failure if the pseudodisk was not able to be 
       reformatted properly.
    */
    writeRet = fwrite(diskBytes, size, numElements, fd);
    if(writeRet != DISK_SIZE) {
        fclose(fd);
        return 0;
    }
    
    /* No errors: close disk and return success */ 
    fclose(fd);
    return 1;
}
