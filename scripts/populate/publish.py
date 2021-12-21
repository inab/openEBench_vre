#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Created on Mon Nov  9 10:34:56 2020

Script to create a record (DOI) in B2SHARE traning enviroment from a json doc with all metadata
and add a file(s) to this record. Finally publish the record.

Workflow:
1. Create draft
2. Upload files into draft record
2.(Update record metadata)
3. Submit for publication

@author: txellfe
"""
import sys
import requests
import json


#test env
token = "ixQFTHFCUIPTjaBRps6ixPjLAo40M8fCE6AR6lEsttokUuS7q8xP2pLnD7Is"
host = "eudat-b2share-test.csc.fi"

#OpenEBench community ID:b3b2f331-7cf8-44c5-a15a-1dae9dca48f6
#Prod enviroment host: b2share.eudat.eu



def main():

    if len(sys.argv) > 1:
        #token 
        token = sys.argv[1]

        #json to upload
        x = sys.argv[2]
        metadata = json.loads(x)

        #create draft record from json
        getResult, created = createDraftRecord(metadata)
        if (created):
            bucketID = fileBucketID(getResult)
            ID = recordID(getResult)
            
            #DOI with files to add
            if len(sys.argv) > 3:
                files = len(sys.argv)-3
                count = 0
                for i in range(3, len(sys.argv)):
                    fileToAddOnDOI = sys.argv[i]
                    #add files to record
                    r = addFileToRecord(bucketID, fileToAddOnDOI)
                    if (r):
                        count += 1
                if (files != count):
                    print("Not all files correctly upload")
            
            #to publish
            published, DOI = updateMetadata(ID)
            if(published):
                #print("Record correcltly published")
                print (DOI)
            else:
                #print("Record NOT correcltly published")
                print ("0")
            
        else:
            #print("Error creating DOI")
            print ("0")
        
        
    else:
        #print("Error - Not correctly introduce arguments")
        print ("0")

    

def createDraftRecord (jsonMetadata):
    """
    Creates a draft reacord on B2SHARE 

    Returns
    -------
    created : TYPE boolean
        DESCRIPTION. true if correcltly created, false otherwise
    response : TYPE string
        DESCRIPTIONS: answer of the petition
    """
    created = False
        
    header = {"Content-Type": "application/json"}
    url = "https://"+host+"/api/records/"
    response = requests.request("POST", url, params={'access_token': token}, data=json.dumps(jsonMetadata),headers=header)

    if (response.status_code == 201):
           
        created = True
             
    elif (response.status_code == 401):
        sys.exit()

    return response, created
    
    


def fileBucketID(responseUpload):
    """
    Gets identifier for a set of files of draft record

    Parameters
    ----------
    responseUpload : TYPE 
        DESCRIPTION.The answer of the upload file (draft)

    Returns
    -------
    bucket: TYPE string
        DESCRIPTION the identifier of a set of files

    """
    
    result = json.loads(responseUpload.text)
    fileBucketID = result["links"]["files"]
    bucket = fileBucketID.rsplit('/', 1)[-1]
    return bucket

def recordID(responseUpload):
    """
    Gets identifier of the draft record DOI

    Parameters
    ----------
    responseUpload : TYPE
        DESCRIPTION.The answer of the upload file (draft)

    Returns
    -------
    ID : TYPE string
        DESCRIPTION: the id of the register to publish

    """
    
    result = json.loads(responseUpload.text)
    ID = result["id"]

    return ID



def addFileToRecord(fileBucketID, fileToAdd):
    """
    Links a given file to the draft record

    Parameters
    ----------
    fileBucketID : TYPE string
        DESCRIPTION. Id of the set of files 
    fileToAdd : TYPE string
        DESCRIPTION. file to be added

    Returns
    -------
    true if file correctly added or false otherwise

    """
    filename = fileToAdd.split("/")[-1]
    try: 
        url = "https://"+host+"/api/files/"+fileBucketID+"/"+filename+"?access_token="+token
    
        payload = open(fileToAdd, "r")
        payload = payload.read()
        
        headers = {
          'Accept': 'application/json',
          'Content-Type': 'application/octet-stream'
        }
        
        response = requests.request("PUT", url, headers=headers, data = payload)
        #saveToFile(path, response)
        if (response):
            return True
        else:
            return False
            
    except IOError:
        return False

    
    
#to publication state
def updateMetadata(draftID):
    """
    Update metadata record to published state

    Parameters
    ----------
    draftID : TYPE string
        DESCRIPTION. Record's DOI to update

    Returns
    -------
    DOI: TYPE string
        DESCRIPTION. Record's DOI 
    published: TYPE boolean
        DESCRIPTION. true if correctly published or false otherwise

    """

    published = False
    DOI = ""

    url = "https://"+host+"/api/records/"+draftID+"/draft?access_token="+token
    
    payload = "[{\"op\": \"add\", \"path\":\"/publication_state\", \"value\": \"submitted\"}]"
    headers = {
      'Content-Type': 'application/json-patch+json'
    }
    response = requests.request("PATCH", url, headers=headers, data = payload)
    #saveToFile(path, response)
    if (response.status_code == 200):
        published = True
        #get doi
        result = json.loads(response.text)
        DOI = result["metadata"]["$future_doi"]

    return published, DOI
    


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
        file = open(filepath, "a")
        file.write(data.text)
        file.close()
        return True
    
    except OSError:
        print ("Error to save to file")
        return False




if __name__ == "__main__":
    main()
