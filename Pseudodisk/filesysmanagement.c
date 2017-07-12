/********************************************************************
* Csci 360 Lab #4
*********************************************************************
* File:
*     filesysmanagement.c
*
* Purpose:
*     Contains the implementations for the functions that both create 
*     and operate the file system that is resident on the pseudodisk. 
*     Makes use of the pseudodisk functions to do so, which are defined 
*     in "pdiskmanagement.h". 
*
* Notes:
*     Permissions for files, and the existance of multiple users is
*     not considered for this program. When a file is opened, the
*     file automatically may be read, written to, or appended to 
*     (there is no variable to specify only one or some actions).
*     Seeking in an open file is always in relation to the start of
*     the file. Options to offset from current position or end of
*     of file do not exist at this time. Files may not be renamed.
*     Files can currently only have 1 character long names.
*    
*     File system functions are currently only functional for
*     pseudodisk sizes of 256 disk blocks or less (within reason),
*     where the size is a power of 2.
*
*     The ability to delete files is not currently available.
*
*     All files are created in the root directory. The ability to
*     create other directories and create files inside of those is
*     not available at this time.  
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
#include <math.h>
#include <stdlib.h>
#include "filesysmanagement.h"

/* Defines */
typedef struct fileData {
    Inode inode;
    Filedescript fileDescript;
    unsigned short headLoc;    
    char contents[MAX_FILE_SIZE];
} FileData; 

/* Globals */
unsigned char numOpenFiles;
FileData *openFiles[MAX_NUM_OPEN_FILES];
Superblock superBlock;

/* Private Function Prototypes */
int getRootDirectoryFromDisk(Directory *dir);
int getInodeFromDisk(Inode *inode, unsigned char inodeNum);
int getFileFromDisk(FileData *fileData);
int getSuperBlockFromDisk();
int getMemoryBitmapFromDisk(MemoryBitmap bitmap);

int writeRootDirectoryToDisk(Directory *dir);
int writeInodeToDisk(Inode *inode, unsigned char inodeNum);
int writeFileToDisk(FileData *fileData);
int writeSuperBlockToDisk(Superblock *sBlock);
int writeMemoryBitmapToDisk(MemoryBitmap bitmap);

int getFreeBlock(unsigned char *blockNum);

/* Function Implementations */
int fileSysCreate()
{
    int i;
    unsigned char startOfDisk, numBlocksBoot, numBlocksSuper, numBlocksBitmap;
    unsigned char numBlocksInodes, numBlocksRoot;
    size_t numElems;

    /* used to show whether last block of section is partial or full data. 
       1 for full data, 0 for partial data.
    */
    unsigned char lastBlockStatusBoot, lastBlockStatusSuper, lastBlockStatusBitmap;
    unsigned char lastBlockStatusInodes, lastBlockStatusRoot; 
      
    /* open pseudodisk file if it isn't already open.
       If operation fails, return failure
    */
    if(!diskOpen()) {
        return 0;
    }    

    startOfDisk = (unsigned char)0;

    /* seek to start of disk */
    if(!diskSeek(startOfDisk)) {
        return 0;
    }

    /* create bootblock values */
    Bootblock bootBlock;
    for(i = 0; i < sizeof(Bootblock); i++) {
        bootBlock.data[i] = (unsigned char)1;
    }

    /* calculate number of disk blocks needed by boot block */
    if(((unsigned char)sizeof(Bootblock) % DISK_BLOCK_SIZE) == (unsigned char)0) {
        numBlocksBoot = (unsigned char)(sizeof(Bootblock) / DISK_BLOCK_SIZE);
        lastBlockStatusBoot = (unsigned char)1;
    }
    else {
        numBlocksBoot = (unsigned char)((sizeof(Bootblock) / DISK_BLOCK_SIZE) + 1);
        lastBlockStatusBoot = (unsigned char)0;
    }

    /* write bootblock to pseudodisk */
    for(i = 0; i < numBlocksBoot; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* block is last block, and only partially full */
        if((i >= (numBlocksBoot - 1)) && (lastBlockStatusBoot == (unsigned char)0)) {
            numElems = (size_t)(sizeof(Bootblock) % DISK_BLOCK_SIZE);
            memcpy(dBlock, (&bootBlock + (i * DISK_BLOCK_SIZE)), numElems);
        }
        /* block is full */
        else {
            memcpy(dBlock, (&bootBlock + (i * DISK_BLOCK_SIZE)), sizeof(Diskblock));
        }
        /* write current block to pseudodisk and then free it */
        if(!diskWrite(*dBlock)) {
            return 0;
        }
        free(dBlock);
    }

    /* calculate number of disk blocks needed by superblock */
    if((unsigned char)(sizeof(Superblock) % DISK_BLOCK_SIZE) == (unsigned char)0) { 
        numBlocksSuper = (unsigned char)(sizeof(Superblock) / DISK_BLOCK_SIZE);  
        lastBlockStatusSuper = (unsigned char)1;   
    }
    else {
        numBlocksSuper = (unsigned char)((sizeof(Superblock) / DISK_BLOCK_SIZE) + 1);
        lastBlockStatusSuper = (unsigned char)0;
    }

    /* calculate number of disk blocks needed by memory bitmap */
    if((unsigned char)(sizeof(MemoryBitmap) % DISK_BLOCK_SIZE) == (unsigned char)0) { 
        numBlocksBitmap = (unsigned char)(sizeof(MemoryBitmap) / DISK_BLOCK_SIZE);
        lastBlockStatusBitmap = (unsigned char)1;     
    }
    else {
        numBlocksBitmap = (unsigned char)((sizeof(MemoryBitmap) / DISK_BLOCK_SIZE) + 1);
        lastBlockStatusBitmap = (unsigned char)0;
    }

    /* calculate number of disk blocks needed by Inodes */
    if((unsigned char)((sizeof(Inode) * NUM_INODES) % DISK_BLOCK_SIZE) == (unsigned char)0) {        
        numBlocksInodes = (unsigned char)((sizeof(Inode) * NUM_INODES) / DISK_BLOCK_SIZE); 
        lastBlockStatusInodes = (unsigned char)1;    
    }
    else {
        numBlocksInodes = (unsigned char)(((sizeof(Inode) * NUM_INODES) / DISK_BLOCK_SIZE) + 1);
        lastBlockStatusInodes = (unsigned char)0;
    }

    /* calculate number of disk blocks needed by root directory */
    if((unsigned char)(sizeof(Directory) % DISK_BLOCK_SIZE) == (unsigned char)0) { 
        numBlocksRoot = (unsigned char)(sizeof(Directory) / DISK_BLOCK_SIZE);
        lastBlockStatusRoot = (unsigned char)1;     
    }
    else {
        numBlocksRoot = (unsigned char)((sizeof(Directory) / DISK_BLOCK_SIZE) + 1);
        lastBlockStatusRoot = (unsigned char)0;
    }
                                                   
    /* create superblock values */

    unsigned short numUsedBlocks; 
    numUsedBlocks = (unsigned short)(numBlocksBoot + numBlocksSuper + numBlocksBitmap + numBlocksInodes + numBlocksRoot);

    superBlock.numBlocks = (unsigned short)(FILE_SYSTEM_SIZE / FILE_BLOCK_SIZE);
    superBlock.numFreeBlocks = (superBlock.numBlocks - numUsedBlocks); 
    superBlock.numInodes = (unsigned char)NUM_INODES;
    superBlock.numFreeInodes = (unsigned char)NUM_INODES;
    superBlock.numFiles = (unsigned char)0;
    superBlock.numDirs = (unsigned char)0; /* directories other than root */
    superBlock.numBlocksBoot = numBlocksBoot;
    superBlock.numBlocksSuper = numBlocksSuper;
    superBlock.numBlocksMemoryBitmap = numBlocksBitmap;
    superBlock.numBlocksInodes = numBlocksInodes;
    superBlock.numBlocksRoot = numBlocksRoot;
    superBlock.lastBlockStatusBoot = lastBlockStatusBoot;
    superBlock.lastBlockStatusSuper = lastBlockStatusSuper;
    superBlock.lastBlockStatusBitmap = lastBlockStatusBitmap;
    superBlock.lastBlockStatusInodes = lastBlockStatusInodes;
    superBlock.lastBlockStatusRoot = lastBlockStatusRoot;

    unsigned char bitmapStrt, inodesStrt, rootDirStrt, fAndDStrt;

    bitmapStrt = (numBlocksBoot + numBlocksSuper);
    inodesStrt = (bitmapStrt + numBlocksBitmap);
    rootDirStrt = (inodesStrt + numBlocksInodes);
    fAndDStrt = (rootDirStrt + numBlocksRoot);

    superBlock.memoryBitmapStart = bitmapStrt; 
    superBlock.inodesStart = inodesStrt; 
    superBlock.rootDirStart = rootDirStrt;
    superBlock.filesAndDirsStart = fAndDStrt; 

    /* write superblock to pseudodisk */
    writeSuperBlockToDisk(&superBlock);

    /* create memory bitmap values (free blocks are represented by 1's) */
    MemoryBitmap memBitmap;
    int numBitsLeft;

    for(i = 0; i < sizeof(MemoryBitmap); i++) {
        /* write initial used block bit values, 8 at a time */
        if(i < (numUsedBlocks / BYTE)) { 
            memBitmap[i] = (unsigned char)0;    
        }
        /* write any leftover used block bit values and following free block
           bit values for the next byte.
        */
        else if((i == (numUsedBlocks / BYTE)) && ((numUsedBlocks % BYTE) != 0)) {
            numBitsLeft = (numUsedBlocks % BYTE);
            memBitmap[i] = (unsigned char)(pow(2,8) - pow(2,(8-numBitsLeft)));       
        }
        /* write remaining blocks as free block bit values */
        else {
            memBitmap[i] = (unsigned char)0xFF;
        }
    }

    /* write memory bitmap to pseudodisk */
    if(!writeMemoryBitmapToDisk(memBitmap)) {
        return 0;
    }

    /* Update pseudodisk to reflect recently written data */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


int fileSysStartup()
{
    /* open pseudodisk file if it isn't already open.
       If operation fails, return failure. 
    */
    if(!diskOpen()) {
        return 0;
    }

    /* load super block into memory */
    if(!getSuperBlockFromDisk()) {
        return 0;
    }

    /* initialize number of open files */
    numOpenFiles = 0;

    /* return success */
    return 1;         
}


int fileSysShutdown() 
{
    /* write the superblock from memory back to the pseudodisk */
    if(!writeSuperBlockToDisk(&superBlock)) {
        return 0;
    }

   /* close pseudodisk */
    if(!diskClose()) {
        return 0;
    }

    /* set number of open files */
    numOpenFiles = 0;

    /* return success */
    return 1;
}    
    

int fileCreate(char *dir, char *fileName, char fileType)
{

    /* check to see that directory desired is ROOT */
    if(strcmp(dir, "ROOT") != 0) {
        return 0;
    } 

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }

    /* check to see that file type is a supported type */
    if(!((fileType == 'f') || (fileType == 'd'))) {
        return 0;
    }

    /* check to see that there are still inodes remaining */
    if(superBlock.numFreeInodes == (unsigned char)0) {
        return 0;
    }

    Directory rootDir;

    /* read root directory into memory */
    getRootDirectoryFromDisk(&rootDir);

    /* check to see if root directory has room for another file */
    if (rootDir.numFiles >= MAX_NUM_FILES) {
        return 0;
    }

    /* create inode data for new file */
    Inode fileInode;

    fileInode.fileSize = (unsigned short)0;
    
    /* create file description to add to root directory listing */
    Filedescript fileDesc;

    memcpy(fileDesc.fileName, fileName, (size_t)(strlen(fileName) + 1));

    fileDesc.fileType = fileType;
    fileDesc.inodeNum = (unsigned char)((superBlock.numInodes - superBlock.numFreeInodes) + 1);

    /* update root directory data in memory */
    rootDir.file[rootDir.numFiles] = fileDesc;
    rootDir.numFiles ++;

    /* update super block data in memory */
    superBlock.numFreeInodes --;

    if(fileType == 'f') {
        superBlock.numFiles ++;
    }
    else if (fileType == 'd') {
        superBlock.numDirs ++;
    }
    
    /* write updated root dir back to the pseudodisk */
    writeRootDirectoryToDisk(&rootDir);

    /* write new inode to pseudodisk */
    writeInodeToDisk(&fileInode, fileDesc.inodeNum);

    /* return success */
    return 1;
}


int fileDelete(char *dir, char *fileName)
{
    /* The ability to delete files isn't available at this
       point in time.
    */
}


int fileOpen(char *dir, char *fileName)
{
    int i;
    size_t numElems;

    /* check to see that directory desired is ROOT */
    if(strcmp(dir, "ROOT") != 0) {
        return 0;
    } 

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }
   
    /* check to see that file isn't already open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp((openFiles[i]->fileDescript.fileName), fileName) == 0) {
            return 1;
        }
    }
   
    /* check to see that the maximum number of open files isn't already met */
    if(numOpenFiles >= MAX_NUM_OPEN_FILES) {
        return 0;
    }

    /* get root directory from pseudodisk */
    Directory rootDir;
    if(!getRootDirectoryFromDisk(&rootDir)) {
        return 0;
    }

    /* find file entry in root directory (if exists) */
    for(i = 0; i < rootDir.numFiles; i++) {
        if(strcmp(rootDir.file[i].fileName, fileName) == 0) {
            /* file found, break early */
            break;
        }
    }
    
    /* check to see that file was found */
    if(i == rootDir.numFiles) {
        /* file not found */
        return 0;
    }

    /* file found, start creating file in memory */    
    FileData *fileData = (FileData *)malloc(sizeof(FileData));
    fileData->fileDescript = rootDir.file[i];
    fileData->headLoc = (unsigned short)0;

    /* get inode for file to be opened */
    if(!getInodeFromDisk(&(fileData->inode), fileData->fileDescript.inodeNum)) {
        return 0;
    }   
     
    /* get file contents from disk */
    if(!getFileFromDisk(fileData)) {
        return 0;
    }

    /* add file to list of open files */
    openFiles[numOpenFiles] = fileData;
    numOpenFiles ++;

    return 1;
}


int fileClose(char *fileName)
{
    int i;

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }
   
    /* check to see that file is currently open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp((openFiles[i]->fileDescript.fileName), fileName) == 0) {
            /* file is open, break and continue */
            break;
        }
    }

    if(i == numOpenFiles) {
        /* file isn't open, exit */
        return 0;
    }

    /* file is open */
    FileData *fileData = openFiles[i]; 
    Inode filesOldInode;
    int openFileLocation = i;

    /* get files old inode entry from pseudodisk */
    if(!getInodeFromDisk(&filesOldInode, fileData->fileDescript.inodeNum)) {
        return 0;
    }   

    /* calculate number of blocks used by the file currently vs number blocks used 
       by the file on the pseudodisk. 
    */
    unsigned char numBlocksCurrent, numBlocksOld;

    if((fileData->inode.fileSize % DISK_BLOCK_SIZE) == 0) {
        numBlocksCurrent = (unsigned char)(fileData->inode.fileSize / DISK_BLOCK_SIZE);       
    }
    else {
        numBlocksCurrent = (unsigned char)((fileData->inode.fileSize / DISK_BLOCK_SIZE) + 1);
    }

    if((filesOldInode.fileSize % DISK_BLOCK_SIZE) == 0) {
        numBlocksOld = (unsigned char)(filesOldInode.fileSize / DISK_BLOCK_SIZE);       
    }
    else {
        numBlocksOld = (unsigned char)((filesOldInode.fileSize / DISK_BLOCK_SIZE) + 1);
    }

    unsigned char newBlockNum;

    /* determine whether new blocks are needed to store file back on pseudodisk. 
       If so, get them, and add them to the block list of the current inode for
       the file.
    */
    if(numBlocksCurrent > numBlocksOld) {
        for(i = 0; i < (numBlocksCurrent - numBlocksOld); i++) {
            if(!getFreeBlock(&newBlockNum)) {
                printf("No Free Blocks Left. Copy Failed. \n");
                return 0;
            }
            else {
                fileData->inode.blockNumList[i + numBlocksOld] = newBlockNum;
            }
        }
    }

    /* update inode on pseudodisk for file to the one in memory */
    if(!writeInodeToDisk(&fileData->inode, fileData->fileDescript.inodeNum)) {
        return 0;
    }
            
    /* write current file back to pseudodisk */
    if(!writeFileToDisk(fileData)) {
        return 0;
    }

    /* remove file from list of open files */
    free(openFiles[openFileLocation]);
    openFiles[openFileLocation] = NULL;

    /* if file closed wasn't the last one in the list of open files, shift
       the pointers of open files over to remove the gap in the list.
    */
    if(numOpenFiles != (openFileLocation + 1)) {
        for(i = openFileLocation; i < (numOpenFiles - 1); i++) {
            openFiles[i] = openFiles[i+1];    
        }
    }

    numOpenFiles--;

    /* return success */
    return 1;
}


unsigned short fileRead(char *fileName, unsigned short numChars, char *charsRead)
{
    int i;

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }

    /* check to see that the number of chars to read is valid */
    if((numChars <= 0) || (numChars > MAX_FILE_SIZE)) {
        return 0;
    }

    /* check to see that the file is currently open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp(openFiles[i]->fileDescript.fileName, fileName) == 0) {
            /* file is open, break early */
            break;
        }
    }

    if(i >= numOpenFiles) {
        /* file is not open */
        return 0;
    }

    /* file is open */
    FileData *fileData = openFiles[i];
    unsigned short newHeadLoc = fileData->headLoc;

    /* determine how many chars are left from head loc in file */
    unsigned short numCharsLeftInFile = (fileData->inode.fileSize - fileData->headLoc);
     
    /* read chars from file from current head position
       (either num passed in, or until end of file).
    */
    for(i = 0; (i < numChars) && (i < numCharsLeftInFile); i++) {
        charsRead[i] = fileData->contents[i + (fileData->headLoc)];
        newHeadLoc++;
    }

    /* add NULL to make chars read a string */        
    charsRead[i] = '\0';

    /* set new file head location */
    fileData->headLoc = newHeadLoc;

    /* return number of chars read */
    return i;
}


unsigned short fileWrite(char *fileName, unsigned short numChars, char *charsToWrite)
{
    int i;

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }

    /* check to see that the number of chars to write is valid */
    if((numChars <= 0) || (numChars > MAX_FILE_SIZE)) {
        return 0;
    }

    /* check to see that the file is currently open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp(openFiles[i]->fileDescript.fileName, fileName) == 0) {
            /* file is open, break early */
            break;
        }
    }

    if(i >= numOpenFiles) {
        /* file is not open */
        return 0;
    }

    /* file is open */
    FileData *fileData = openFiles[i];
    unsigned short newHeadLoc = fileData->headLoc;
    unsigned short newFileSize = fileData->inode.fileSize;

    /* determine how many chars are left from head loc to MAX_FILE_SIZE */
    unsigned short numCharsLeftInFile = (MAX_FILE_SIZE - fileData->headLoc);

    /* write chars to file from current head position
       (either num passed in, or til max file size is reached).
    */
    for(i = 0; (i < numChars) && (i < numCharsLeftInFile); i++) {
        fileData->contents[i + (fileData->headLoc)] = charsToWrite[i];
        if(newHeadLoc >= fileData->inode.fileSize) {
            newFileSize++;
        }
        newHeadLoc++;
    }
            
    /* set new file size */
    fileData->inode.fileSize = newFileSize;

    /* set new file head location */
    fileData->headLoc = newHeadLoc;

    /* return number of chars written */
    return i;
}


unsigned short fileSeek(char *fileName, unsigned short offset)
{
    int i;

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return 0;
    }

    /* check to see that the file is currently open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp(openFiles[i]->fileDescript.fileName, fileName) == 0) {
            /* file is open, break early */
            break;
        }
    }

    if(i >= numOpenFiles) {
        /* file is not open */
        return 0;
    }

    /* file is open */ 
    FileData *fileData = openFiles[i];

    /* seek the head some positions (relative to start of file)
       (either offset passed in, or until end of file).
    */
    fileData->headLoc = 0; /* reset head location to beginning */
    for(i = 0; (i < offset) && (i < fileData->inode.fileSize); i++) {
        (fileData->headLoc)++;
    }
            
    /* return number of positions seeked */
    return i;
}


int getFileSize(char *fileName)
{
    int i;

    /* check to see that file name is of appropriate length */
    if((strlen(fileName) > MAX_FILENAME_SIZE) || (strlen(fileName) < 1)) {
        return -1;
    }

    /* check to see that the file is currently open */
    for(i = 0; i < numOpenFiles; i++) {
        if(strcmp(openFiles[i]->fileDescript.fileName, fileName) == 0) {
            /* file is open, break early */
            break;
        }
    }

    if(i >= numOpenFiles) {
        /* file is not open */
        return -1;
    }

    /* file is open */

    /* return file size */
    return (openFiles[i]->inode.fileSize);    
}


int fileAppend(char *fileName1, char *fileName2, char *newFile)
{
    /* check to see that file name for file1 is of appropriate length */
    if((strlen(fileName1) > MAX_FILENAME_SIZE) || (strlen(fileName1) < 1)) {
        return 0;
    }
    
    /* check to see that file name for file2 is of appropriate length */
    if((strlen(fileName2) > MAX_FILENAME_SIZE) || (strlen(fileName2) < 1)) {
        return 0;
    }

    /* check to see that file name for newFile is of appropriate length */
    if((strlen(newFile) > MAX_FILENAME_SIZE) || (strlen(newFile) < 1)) {
        return 0;
    }

    /* open file 1 */
    if(!fileOpen("ROOT", fileName1)) {
        return 0;
    }

    /* open file 2 */
    if(!fileOpen("ROOT", fileName2)) { 
        return 0;
    }

    /* create new file */
    if(!fileCreate("ROOT", newFile, 'f')) {
        return 0;
    }

    /* open new file */
    if(!fileOpen("ROOT", newFile)) {
        return 0;
    }

    /* get file size of file 1 */
    int file1Size = getFileSize(fileName1); 

    /* get file size of file 2 */
    int file2Size = getFileSize(fileName2);

    char file1Contents[file1Size+1];    
    char file2Contents[file2Size+1];

    /* read file 1 contents */
    fileRead(fileName1, file1Size, file1Contents);

    /* read file 2 contents */
    fileRead(fileName2, file2Size, file2Contents);
    
    /* copy file 1 contents to start of new file */
    fileWrite(newFile, file1Size, file1Contents);
 
    /* copy file 2 contents to end of new file */
    fileWrite(newFile, file2Size, file2Contents);

    /* close files */
    if(!fileClose(fileName1)) {
        return 0;
    }
    if(!fileClose(fileName2)) {
        return 0;
    }
    if(!fileClose(newFile)) {
        return 0;
    }

    /* return success */
    return 1;
}


/*************************************
*  Private Function Implementations  *
*************************************/

/* Get root directory from pseudodisk and store it 
   in memory location pointed to by "dir".
   Returns 1 on success, 0 on failure.
*/
int getRootDirectoryFromDisk(Directory *dir)
{
    int i;
    unsigned char numElems;

    /* seek pseudodisk to start of root directory */
    if(!diskSeek(superBlock.rootDirStart)) {
        return 0;
    }
 
    /* read root directory from pseudodisk */
    for(i = 0; i < superBlock.numBlocksRoot; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* read a disk block of root dir from pseudodisk into memory */
        if(!diskRead(*dBlock)) {
            return 0;
        }
        /* block is last block, and only partially full */
        if((i >= (superBlock.numBlocksRoot - 1)) && (superBlock.lastBlockStatusRoot == (unsigned char)0)) {
            numElems = (size_t)(sizeof(Directory) % DISK_BLOCK_SIZE);
            memcpy((dir + (i * DISK_BLOCK_SIZE)), dBlock, numElems);
        }
        /* block is full */
        else {
            memcpy((dir + (i * DISK_BLOCK_SIZE)), dBlock, sizeof(Diskblock));
        }
        /* free disk block */
        free(dBlock);
    }

    /* return success */
    return 1;
}


/* Get Inode number "inodeNum" from the pseudodisk and
   store it in the memory location pointed to by "inode".
   Returns 1 on success, 0 on failure.
*/
int getInodeFromDisk(Inode *inode, unsigned char inodeNum)
{
    /* seek to start of right inode location */
    unsigned char writeOffset = (unsigned char)(((inodeNum - 1) * sizeof(Inode)) / DISK_BLOCK_SIZE);
    if(!diskSeek(superBlock.inodesStart + writeOffset)) {
        return 0;
    }

    /* get existing inode data block */
    Diskblock inodeBlock;
    if(!diskRead(inodeBlock)) {
        return 0;
    } 

    /* extract inode from inode-block */
    unsigned char cpyOffset = (unsigned char)(((inodeNum - 1) * sizeof(Inode)) % DISK_BLOCK_SIZE);
    memcpy(inode, (inodeBlock + cpyOffset), sizeof(Inode));

    /* return success */
    return 1;
}


/* Get file contents of the file whose inode is contained in the 
   memory location pointed to by "fileData" from the pseudodisk, 
   and store it in the correct location inside the memory location 
   pointed to by "fileData". 
   Returns 1 on success, 0 on failure.
*/
int getFileFromDisk(FileData *fileData)
{
    int i;
    unsigned char numElems, lastBlockStatus;
    unsigned short numBlocksForFile;

    /* determine how many blocks the file takes up */
    if((fileData->inode.fileSize % DISK_BLOCK_SIZE) == 0) { 
        numBlocksForFile = (fileData->inode.fileSize / DISK_BLOCK_SIZE);
        lastBlockStatus = (unsigned char)1;
    }
    else {
        numBlocksForFile = ((fileData->inode.fileSize / DISK_BLOCK_SIZE) + 1);
        lastBlockStatus = (unsigned char)0;
    }

    /* retrieve file contents */
    for(i = 0; i < numBlocksForFile; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* seek to next block in file */
        if(!diskSeek(fileData->inode.blockNumList[i])) {
            return 0;
        }
        /* read a disk block of file from pseudodisk into memory */
        if(!diskRead(*dBlock)) {
            return 0;
        }
        /* block is last block, and only partially full */
        if((i >= (numBlocksForFile - 1)) && (lastBlockStatus == (unsigned char)0)) {
            numElems = (size_t)(fileData->inode.fileSize % DISK_BLOCK_SIZE);
            memcpy((fileData->contents + (i * DISK_BLOCK_SIZE)), dBlock, numElems);
        }
        /* block is full */
        else {
            memcpy((fileData->contents + (i * DISK_BLOCK_SIZE)), dBlock, sizeof(Diskblock));
        }
        /* free disk block */
        free(dBlock);
    }

    /* return success */
    return 1;
}


/* Loads the data of the super block from the pseudodisk into memory.
   Assumes: the pseudodisk file is already open.
   Returns 1 if successful, and 0 otherwise.
*/
int getSuperBlockFromDisk()
{
    Diskblock blockToRead;
    unsigned char superBlockLoc, tempLoc;    

    tempLoc = (unsigned char)(sizeof(Bootblock) / DISK_BLOCK_SIZE);

    /* get block number of start of super block */
    if((unsigned char)(sizeof(Bootblock) % DISK_BLOCK_SIZE) == (unsigned char)0) {
        superBlockLoc = tempLoc;
    }
    else { 
        superBlockLoc = (unsigned char)(tempLoc + 1);
    }
    
    /* seek to start of superblock */
    if(!diskSeek(superBlockLoc)) {
        return 0;
    }
    
    /* load superblock into memory */
    if(!diskRead(blockToRead)) {
        return 0;
    }
    memcpy(&superBlock, blockToRead, sizeof(Superblock)); 
    
    /* return success */
    return 1;
}


/* Gets the Memory Bitmap from the pseudodisk and stores it in the memory
   location pointed to by "bitmap".
   Returns 1 on success, and 0 on failure.
*/
int getMemoryBitmapFromDisk(MemoryBitmap bitmap)
{
    int i;
    unsigned char numElems;

    /* seek pseudodisk to start of memory bitmap */
    if(!diskSeek(superBlock.memoryBitmapStart)) {
        return 0;
    }
 
    /* read Memory Bitmap from pseudodisk */
    for(i = 0; i < superBlock.numBlocksMemoryBitmap; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* read a disk block of memory bitmap from pseudodisk into memory */
        if(!diskRead(*dBlock)) {
            return 0;
        }
        /* block is last block, and only partially full */
        if((i >= (superBlock.numBlocksMemoryBitmap - 1)) && (superBlock.lastBlockStatusBitmap == (unsigned char)0)) {
            numElems = (size_t)(sizeof(MemoryBitmap) % DISK_BLOCK_SIZE);
            memcpy((bitmap + (i * DISK_BLOCK_SIZE)), dBlock, numElems);
        }
        /* block is full */
        else {
            memcpy((bitmap + (i * DISK_BLOCK_SIZE)), dBlock, sizeof(Diskblock));
        }
        /* free disk block */
        free(dBlock);
    }

    /* return success */
    return 1;
}


/* Write the root directory from memory back to the 
   pseudodisk.
   Returns 1 on success, 0 on failure.
*/      
int writeRootDirectoryToDisk(Directory *dir)
{
    int i;
    size_t numElems;

    /* seek to start of root dir */
    if(!diskSeek(superBlock.rootDirStart)) {
        return 0;
    }

    /* write root dir back to the pseudodisk */
    for(i = 0; i < superBlock.numBlocksRoot; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* block is last block, and only partially full */
        if((i >= (superBlock.numBlocksRoot - 1)) && (superBlock.lastBlockStatusRoot == (unsigned char)0)) {
            numElems = (size_t)(sizeof(Directory) % DISK_BLOCK_SIZE);
            memcpy(dBlock, (dir + (i * DISK_BLOCK_SIZE)), numElems);
        }
        /* block is full */
        else {
            memcpy(dBlock, (dir + (i * DISK_BLOCK_SIZE)), sizeof(Diskblock));
        }
        /* write current block to pseudodisk and then free it */
        if(!diskWrite(*dBlock)) {
            return 0;
        }
        free(dBlock);
    }

    /* save changes made to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


/* Write the inode number "inodeNum" from memory back to
   the pseudodisk.
   Returns 1 on success, 0 on failure.
*/
int writeInodeToDisk(Inode *inode, unsigned char inodeNum)
{
    /* seek to start of right inode location */
    unsigned char writeOffset = (unsigned char)(((inodeNum - 1) * sizeof(Inode)) / DISK_BLOCK_SIZE);
    if(!diskSeek(superBlock.inodesStart + writeOffset)) {
        return 0;
    }

    /* get existing inode data block */
    Diskblock inodeBlock;
    if(!diskRead(inodeBlock)) {
        return 0;
    } 

    /* update inode block in memory with new values */
    unsigned char cpyOffset = (unsigned char)(((inodeNum - 1) * sizeof(Inode)) % DISK_BLOCK_SIZE);
    memcpy((inodeBlock + cpyOffset), inode, sizeof(Inode));

    /* write new inode data to right location in pseudodisk */
    if(!diskSeek(superBlock.inodesStart + writeOffset)) {
        return 0;
    }
    if(!diskWrite(inodeBlock)) {
        return 0;
    }

    /* save changes made to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


/* Write the file contents stored within the memory location pointed 
   to by "fileData" back to the pseudodisk.
   Returns 1 on success, and 0 on failure.
*/
int writeFileToDisk(FileData *fileData)
{
    int i;
    unsigned char numElems, lastBlockStatus;
    unsigned short numBlocksForFile;

    /* determine how many blocks the file takes up */
    if((fileData->inode.fileSize % DISK_BLOCK_SIZE) == 0) { 
        numBlocksForFile = (fileData->inode.fileSize / DISK_BLOCK_SIZE);
        lastBlockStatus = (unsigned char)1;
    }
    else {
        numBlocksForFile = ((fileData->inode.fileSize / DISK_BLOCK_SIZE) + 1);
        lastBlockStatus = (unsigned char)0;
    }

    /* write file contents to pseudodisk */
    for(i = 0; i < numBlocksForFile; i++) {
        /* seek to next block in file */
        if(!diskSeek(fileData->inode.blockNumList[i])) {
            return 0;
        }
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* block is last block, and only partially full */
        if((i >= (numBlocksForFile - 1)) && (lastBlockStatus == (unsigned char)0)) {
            numElems = (size_t)(fileData->inode.fileSize % DISK_BLOCK_SIZE);
            memcpy(dBlock, (fileData->contents + (i * DISK_BLOCK_SIZE)), numElems);
        }
        /* block is full */
        else {
            memcpy(dBlock, (fileData->contents + (i * DISK_BLOCK_SIZE)), sizeof(Diskblock));
        }
        /* write current block of file to pseudodisk and then free it */
        if(!diskWrite(*dBlock)) {
            return 0;
        }
        free(dBlock);
    }

    /* save changes made to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


/* Write the super block from memory back to the disk.
   Returns 1 on success, 0 on failure.
*/
int writeSuperBlockToDisk(Superblock *sBlock)
{
    int i;
    size_t numElems;
    unsigned char numBlocksBoot = sBlock->numBlocksBoot;
    unsigned char numBlocksSuper = sBlock->numBlocksSuper;
    unsigned char lastBlockStatusSuper = sBlock->lastBlockStatusSuper;

    /* seek to superblock location */
    if(!diskSeek((int)numBlocksBoot)) {
        return 0;
    }

    /* write the super block to pseudodisk */
    for(i = 0; i < (int)numBlocksSuper; i++) {
        Diskblock *dBlock = (Diskblock *)calloc(sizeof(Diskblock), sizeof(unsigned char));
        /* block is last block, and only partially full */
        if((i >= (int)(numBlocksSuper - 1)) && (lastBlockStatusSuper == (unsigned char)0)) {
            numElems = (size_t)(sizeof(Superblock) % DISK_BLOCK_SIZE);
            memcpy(dBlock, (sBlock + (i * DISK_BLOCK_SIZE)), numElems);
        }
        /* block is full */
        else {
            memcpy(dBlock, (sBlock + (i * DISK_BLOCK_SIZE)), sizeof(Diskblock));
        }
        /* write current block to pseudodisk and then free it */
        if(!diskWrite(*dBlock)) {
            return 0;
        }
        free(dBlock);
    }

    /* save changes made to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


/* Writes the memory bitmap in memory pointed to by "bitmap" back to the
   pseudodisk.
   Returns 1 on success, 0 on failure.
*/
int writeMemoryBitmapToDisk(MemoryBitmap bitmap)
{
    int i;
    size_t numElems;

    /* seek to start of memory bitmap */
    if(!diskSeek(superBlock.memoryBitmapStart)) {
        return 0;
    }

    /* write memory bitmap back to the pseudodisk */
    for(i = 0; i < superBlock.numBlocksMemoryBitmap; i++) {
        Diskblock *dBlock = (Diskblock *)malloc(sizeof(Diskblock));
        /* block is last block, and only partially full */
        if((i >= (superBlock.numBlocksMemoryBitmap - 1)) && (superBlock.lastBlockStatusBitmap == (unsigned char)0)) {
            numElems = (size_t)(sizeof(MemoryBitmap) % DISK_BLOCK_SIZE);
            memcpy(dBlock, (bitmap + (i * DISK_BLOCK_SIZE)), numElems);
        }
        /* block is full */
        else {
            memcpy(dBlock, (bitmap + (i * DISK_BLOCK_SIZE)), sizeof(Diskblock));
        }
        /* write current block to pseudodisk and then free it */
        if(!diskWrite(*dBlock)) {
            return 0;
        }
        free(dBlock);
    }

    /* save changes to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}


/* Gets a block number of a free block in memory, and returns it by
   storing it in the location pointed to by "blockNum".
   Returns 1 on success, and 0 if no more free blocks are available,
   or it otherwise failed.
*/
int getFreeBlock(unsigned char *blockNum)
{
    int i, j;

    /* check to see if there are free blocks left */
    if(superBlock.numFreeBlocks <= (unsigned char)0) {
        return 0;
    }

    MemoryBitmap bitmap;

    /* pull memory bitmap into memory */
    if(!getMemoryBitmapFromDisk(bitmap)) {
        return 0;
    }
 
    unsigned char bitMask = (unsigned char)0x80;
    unsigned char currentBlockNum = 0;
    int found = 0;
    unsigned char currentByte;

    /* search for free block */
    for(i = 0; i < (superBlock.numBlocks / BYTE); i++) {
        currentByte = bitmap[i];
        for(j = 0; j < BYTE; j++) {
            if((currentByte & bitMask) != (unsigned char)0) {
                /* free block found, break */
                found = 1;
                break;
            }
            else {
                /* left bit shift */
                currentByte = currentByte << 1;      

                currentBlockNum++;
            }
        }
        if(found) {
            break;
        }
    }   

    /* free block found */
    *blockNum = currentBlockNum;

    /* update memory bitmap */
    bitMask = (unsigned char)((pow(2,8)-1) - pow(2,(7-j)));
    bitmap[i] = (bitmap[i] & bitMask);

    /* update super block */
    superBlock.numFreeBlocks--;

    /* put updated memory bitmap back to pseudodisk */
    if(!writeMemoryBitmapToDisk(bitmap)) {
        return 0;
    }    

    /* save changes made to pseudodisk */
    if(!diskSave()) {
        return 0;
    }

    /* return success */
    return 1;
}
