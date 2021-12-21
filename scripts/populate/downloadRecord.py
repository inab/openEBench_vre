#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Nov  5 19:24:07 2020
Script to get a specific DOI from B2SHARE (to execute by terminal)
Also is getting its linked file(s) if it has

@author: txellfe
"""
import sys
import requests
import json
import wget
import hashlib

#token from txellfe user and prod env
token = "KCSjfwWD6LjHi8zVgP2VYCQ4K3AGWFoAIGSubuKIFjbCQayfx11jumYINjTp"
#Example: 1c48e5950ac94bdaa3fd725ce42ed9cb (4files)
#Example: d4661e6471bc42f58eab2c96b03804f7 (0files)

def main():
    
    if len(sys.argv) == 2:
        #Record ID to download
        inputDOI = sys.argv[1]

        r, done = getSpecificDOI(inputDOI)
        #print(r.text.encode('utf8'))
        #Save to file
        if (done): 
            if (saveToFile("files/"+inputDOI+".json", r)):
                print("Metadata correctly saved")
        
                #get files
                b = getBucketID(r)
                if (b != False):
                    arr, check = getFiles(b)
                    downloadFiles(arr, check, "./files")
                    
                else:
                    print("No files to download")
        else:
            print("DOI does not exist")
            
    
    else:
        print("Error - Not correctly introduce arguments")
        
    




def getSpecificDOI (DOI):
    """
    Gets a specific DOI and save the answer (json data) to a file

    Parameters
    ----------
    DOI : TYPE string
        DESCRIPTION. Id of the record to download

    Returns
    -------
    response : TYPE
        DESCRIPTION answer of the get request

    """
    executed = False
    payload = {}
    headers= {}
    url = "https://b2share.eudat.eu/api/records/"+DOI+"?access_token="+token
    response = requests.request("GET", url, headers=headers, data = payload)
    if (response.status_code == 200):
        executed = True
    
    return response, executed
    

def getBucketID (response):
    result = json.loads(response.text)
    #if there are files attached
    if (len(result["files"]) != 0):
        fileBucketID = result["links"]["files"]
        bucket = fileBucketID.rsplit('/', 1)[-1]
        return bucket
    
    #no files attached
    else:
        return False


def getFiles (bucket):
    url = "https://b2share.eudat.eu/api/files/"+bucket

    payload = {}
    headers= {}
    
    response = requests.request("GET", url, headers=headers, data = payload)
    
    #print(response.text.encode('utf8'))
    
    result = json.loads(response.text)
    
    #get all links to file to download into array
    #get md5 of that files
    listLinksFiles = []
    checksumFiles = []
    for i in range(len(result["contents"])):
        listLinksFiles.append(result["contents"][i]["links"]["self"])
    for i in range(len(result["contents"])):
        checksumFiles.append((result["contents"][i]["checksum"]).rsplit(':',1)[-1])

    return listLinksFiles, checksumFiles

def downloadFiles (arrayLinks, checksumFiles, pathToDownload):
    
    try: 
        #Download files from the links into the array
        for i in range(len(arrayLinks)):
            wget.download(arrayLinks[i], pathToDownload)
            
            if(checkCorruption(pathToDownload+"/"+(arrayLinks[i].rsplit('/', 1)[-1]),checksumFiles[i] )):
                print("Valid file. Successfully downloaded "+arrayLinks[i].rsplit('/', 1)[-1])
            else: 
                #try 3 times to download that file
                for j in range(3):
                    wget.download(arrayLinks[i], pathToDownload)
                    if(checkCorruption(pathToDownload+"/"+(arrayLinks[i].rsplit('/', 1)[-1]),checksumFiles[i] )):
                        print("Valid file. Successfully downloaded "+arrayLinks[i].rsplit('/', 1)[-1])
                        break;
                    else: 
                        print("Unuccessfully downloaded "+i.rsplit('/', 1)[-1])
  
        print("Downloaded "+ str(len(arrayLinks)) + " files")
        
    except IOError:
        print ("\n Error to download file(s)")
        

def saveToFile (filepath, data):
    """
    Save data to a given file

    Parameters
    ----------
    filepath : TYPE string
        DESCRIPTION: path wehre to save the file
    data : TYPE requests.models.Response
        DESCRIPTION: to save

    Returns
    -------
    None.

    """
    try: 
        file = open(filepath, "w")
        file.write(data.text)
        file.close()
        return True
    
    except IOError:
        print ("Error to save metadata file")
        return False
        
#checks if a file is corrupted    
def checkCorruption(filename, original_md5):
    try: 
        with open(filename,"rb") as f:
            bytes = f.read() # read file as bytes
            readable_hash = hashlib.md5(bytes).hexdigest();
            print(readable_hash)
        
        # Finally compare original MD5 with freshly calculated
        if original_md5 == readable_hash:
            return True
        else:
            return False
    except IOError:
        print ("Not possible to check file")
    
    
    
if __name__ == "__main__":
    main()

