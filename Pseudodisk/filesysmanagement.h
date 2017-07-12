/********************************************************************
* Csci 360 Lab #4
*********************************************************************
* File:
*     filesysmanagement.h
*
* Purpose:
*     Contains the prototypes for the functions that both create and 
*     operate the file system that is resident on the pseudodisk, as
*     well as any #defines used. Makes use of the pseudodisk functions 
*     to do so, which are defined in "pdiskmanagement.h".
*
* Notes:
*     Permissions for files, and the existence of multiple users is
*     not considered for this program. When a file is opened, the 
*     file automatically may be read, written to, or appended to 
*     (there is no variable to specify only one or some actions).
*     Seeking in an open file is always in relation to the start of
*     the file. Options to offset from current position or end of 
*     of file do not exist at this time. Files may not be renamed.
*     Files also currently can only have 1 character long names. 
*     
*     Only two types of files exist in this filesystem, a normal
*     file, and a directory file.
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
#include "pdiskmanagement.h"

/* Defines */

/* Same size as diskblock size to make things simple */
#define FILE_BLOCK_SIZE DISK_BLOCK_SIZE 

/* Make file system take up entire disk to keep things simple */  
#define FILE_SYSTEM_SIZE DISK_SIZE

#define NUM_INODES 32
#define NUM_INODE_BLOCK_PTRS 6

/* All sizes in bytes */
#define BOOT_BLOCK_SIZE FILE_BLOCK_SIZE
#define MAX_FILENAME_SIZE 1
#define MAX_FILE_SIZE (NUM_INODE_BLOCK_PTRS * FILE_BLOCK_SIZE) 

/* Maximum number of files in a directory */
#define MAX_NUM_FILES 10

/* Maximum number of open files at a time */
#define MAX_NUM_OPEN_FILES 10

/* num of bits */
#define BYTE 8

typedef struct bootblock {
    unsigned char data[BOOT_BLOCK_SIZE];  
} Bootblock;

typedef struct superblock {
    unsigned short numBlocks; /* number of blocks in the file system */
    unsigned short numFreeBlocks; /* number of free blocks in the file system */
    unsigned char numInodes; /* number of inodes in the file system */
    unsigned char numFreeInodes; /* number of free inodes in the file system */
    unsigned char numFiles; /* number of files in the file system */
    unsigned char numDirs; /* number of dirs in the file system */
    unsigned char numBlocksBoot; /* number of blocks used by boot block */
    unsigned char numBlocksSuper; /* number of blocks used by super block */
    unsigned char numBlocksMemoryBitmap; /* number of blocks used by bitmap */
    unsigned char numBlocksInodes; /* number of blocks used by inodes */
    unsigned char numBlocksRoot; /* number of blocks used by root dir */
    unsigned char lastBlockStatusBoot; /* status of last block of boot */
    unsigned char lastBlockStatusSuper; /* status of last block of super */
    unsigned char lastBlockStatusBitmap; /* status of last block of bitmap */
    unsigned char lastBlockStatusInodes; /* status of last block of inodes */
    unsigned char lastBlockStatusRoot; /* status of last block of root dir */
    unsigned char memoryBitmapStart; /* block number of start of memory bitmap */
    unsigned char inodesStart; /* block number of start of inodes */
    unsigned char rootDirStart; /* block number of start of root dir */
    unsigned char filesAndDirsStart; /* block number of start of files and dirs */
} Superblock;

typedef unsigned char MemoryBitmap[((FILE_SYSTEM_SIZE / FILE_BLOCK_SIZE) / BYTE)];

typedef struct inode {
    unsigned short fileSize; /* file size in bytes */ 
    unsigned char blockNumList[NUM_INODE_BLOCK_PTRS]; /* list of disk block numbers for file */
} Inode;

typedef struct filedescript {
    char fileName[MAX_FILENAME_SIZE + 1];
    char fileType; /* either normal file or dir file */
    unsigned char inodeNum;
} Filedescript;

typedef struct directory {
    unsigned char numFiles;
    Filedescript file[MAX_NUM_FILES]; /* list of filename-inode pairs in dir */
} Directory;

/* Function Prototypes */

/* Creates the file system on the pseudodisk defined in
   "pdiskmanagement.h". Sets up the size of the file system,
   and the size of its various parts according to the
   definitions above, and writes the initial states of the
   file system to the pseudodisk. If the file system already
   exists, it will be overwritten with default values.
   Returns 1 on success, and 0 on failure.
*/
int fileSysCreate();


/* Starts up the file system. Opens the pseudodisk, and then
   readies the file system by loading the superblock into memory.
   Returns 1 on success, and 0 on failure.
*/
int fileSysStartup();


/* Shuts down the file system. Stores the superblock from
   memory back to the disk, and closes the pseudodisk.
   Returns 1 on success, 0 on failure.
*/
int fileSysShutdown();


/* Creates a file in the file system in the directory name pointed to
   by "dir", whose filename is pointed to by "fileName", whose file
   type is defined by "fileType".
   Returns 1 on success, and 0 if the process fails, or the filename 
   already exists.
*/       
int fileCreate(char *dir, char *fileName, char fileType);


/* Deletes a file in the file system in the directory name pointed to
   by "dir", whose filename is pointed to by "fileName". 
   Returns 1 on success, and 0 on failure, or if the filename doesn't 
   exist.
*/
int fileDelete(char *dir, char *fileName);


/* Opens a file in the filesystem for reading, writing, or appending.
   The file's directory name is pointed to by "dir", and the filename is 
   pointed to by "fileName".
   Returns 1 on success, and 0 on failure.
*/ 
int fileOpen(char *dir, char *fileName);


/* Closes a file in the filesystem whose filename is pointed to by 
   "fileName", and saves it back to the pseudodisk.
   Returns 1 on success, and 0 on failure.
*/ 
int fileClose(char *fileName);


/* Reads "numChars" chars of an open file from its current head position.
   The filename is pointed to by "fileName", and the chars read are stored
   in the array pointed to by "charsRead". Will only read up until end of
   file if the number of chars desired is larger than the number of chars
   remaining from the current head position to the end of file. The 
   pointer passed at which is the address that the read chars are stored, 
   must point to memory that is at least of size one char larger than the 
   number of chars desired (to facilitate '\0' being added).
   Returns number of characters successfully read;
*/
unsigned short fileRead(char *fileName, unsigned short numChars, char *charsRead);


/* Writes "numChars" chars to an open file to its current head position.
   The filename is pointed to by "fileName", and the chars written are passed
   by the array pointed to by "charsToWrite". Will only write up to the number
   of characters that keeps the file within the MAX_FILE_SIZE boundary.
   Returns number of characters successfully written.
*/
unsigned short fileWrite(char *fileName, unsigned short numChars, char *charsToWrite);


/* Seeks the head of a file to the number of positions from the start of the file
   specified in "offset", whose filename is pointed to by "fileName". 
   All seeks are always specified in this way, as there is no option to seek from 
   current position or from end of file at this time. If the offset is larger than
   the file size, it will seek to the end of the file.
   Returns number of positions successfully seeked.
*/
unsigned short fileSeek(char *fileName, unsigned short offset);

/* Returns the file size (in bytes) of the open file whose name is pointed to by
   "fileName". If file is not found, or is not open, Returns sentinel: -1. 
*/   
int getFileSize(char *fileName);

/* Appends two files together, the second one to the first, and creates
   a new file.
   Returns 1 if successful, and 0 on failure.
*/
int fileAppend(char *fileName1, char *fileName2, char *newFile); 
