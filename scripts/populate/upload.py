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

#module created by txelfe with functions to validate json docs 
#import validation

#token from txellfe user and test env
#token = "CcPEWY59ylwOFZCIAyI0wyHRlgldSR8dFtPzshbFKnC6A2L1umi7IFP3uO6v"
#token test
token = "ixQFTHFCUIPTjaBRps6ixPjLAo40M8fCE6AR6lEsttokUuS7q8xP2pLnD7Is"
#host = "trng-b2share.eudat.eu"
host = "eudat-b2share-test.csc.fi"

#schemaFileName = "openSchema.json"
#EUDAT community ID:e9b9792e-79fb-4b07-b6b4-b9c2bd06d095
#OpenEBench community ID:b3b2f331-7cf8-44c5-a15a-1dae9dca48f6
#Prod enviroment host: b2share.eudat.eu

#PAth where to save logs


def main():
    
    if len(sys.argv) > 1:
        #json to upload
        fileWithMetadata = sys.argv[1]
        #create draft record from json
        getResult, created = createDraftRecord(fileWithMetadata)
        if (created):
            bucketID = fileBucketID(getResult)
            DOI = recordID(getResult)
            print(DOI)
            
            #DOI with files to add
            if len(sys.argv) > 2:
                files = len(sys.argv)-2
                print(str(files) + " files to add") 
    
                for i in range(2, len(sys.argv)):
                    fileToAddOnDOI = sys.argv[i]
                    #add files to record
                    addFileToRecord(bucketID, fileToAddOnDOI)
            
            #to publish
            #if (validateJson (fileWithMetadata)):
            if(updateMetadata(DOI)):
                print("Record correcltly published")
                print(DOI)
            else:
                print("Record NOT correcltly published")
            
        else:
            print("Error creating DOI")
        
        
    else:
        print("Error - Not correctly introduce arguments")

    

def createDraftRecord (jsonMetadataFile):
    """
    Creates a draft reacord to B2SHARE test enviroment on EUDAT community

    Returns
    -------
    response : TYPE
        DESCRIPTION. answer of the post request

    """
    created = False
    try:
        with open(jsonMetadataFile) as json_file: 
            metadata = json.load(json_file) 
        
        
        header = {"Content-Type": "application/json"}
        url = "https://"+host+"/api/records/"
        response = requests.request("POST", url, params={'access_token': token}, data=json.dumps(metadata),headers=header)
        
        #saveToFile(path, response)
        if (response.status_code == 201):
             #print(response.text.encode('utf8'))
           
             created = True
             
        elif (response.status_code == 401):
             print("Permision denied")
             sys.exit()
        return response, created
    
    except IOError or TypeError:
        print("No such file or directory")
        sys.exit()


def fileBucketID(responseUpload):
    """
    Gets identifier for a set of files of draft record

    Parameters
    ----------
    responseUpload : TYPE
        DESCRIPTION.The answer of the upload file (draft)

    Returns
    -------
    None.

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
    None.

    """
    
    result = json.loads(responseUpload.text)
    DOI = result["id"]

    return DOI



def addFileToRecord(fileBucketID, fileToAdd):
    """
    Links a given file to the draft record

    Parameters
    ----------
    fileBucketID : TYPE string
        DESCRIPTION.
    fileToAdd : TYPE string
        DESCRIPTION.

    Returns
    -------
    None.

    """
    try: 
        url = "https://"+host+"/api/files/"+fileBucketID+"/"+fileToAdd+"?access_token="+token
    
        payload = open(fileToAdd, "r")
        payload = payload.read()
        
        headers = {
          'Accept': 'application/json',
          'Content-Type': 'application/octet-stream'
        }
        
        response = requests.request("PUT", url, headers=headers, data = payload)
        #saveToFile(path, response)
        if (response):
            print("Added file(s) with success")
        else:
            print("error to upload")
            
    except IOError:
        print("File "+fileToAdd+" not found")

    
    
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
    None.

    """

    published = False
    url = "https://"+host+"/api/records/"+draftID+"/draft?access_token="+token
    
    #payload = "[\n    {\"op\": \"add\", \"path\":\"/publication_state\", \"value\": \"submitted\"},\n    { \"op\": \"add\", \"path\": \"/community_specific\", \"value\": {} }\n    \n    ]"
    payload = "[{\"op\": \"add\", \"path\":\"/publication_state\", \"value\": \"submitted\"}]"
    headers = {
      'Content-Type': 'application/json-patch+json'
    }
    response = requests.request("PATCH", url, headers=headers, data = payload)
    #saveToFile(path, response)
    if (response.status_code == 200):
         #print(response.text.encode('utf8'))
         published = True
    
    return published
    


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


def validateJson (file):
    valid = False
    #schemaFileName = "eudatSchema.json"
    # Convert json to python object.
    jsonData = validation.get_schema(file)
    # validate it
    is_valid, msg = validation.validate_json(jsonData, schemaFileName)
    print(msg)
    if (is_valid):
        valid = True
    return valid


def timestamp():
    from datetime import datetime
    # current date and time
    now = datetime.now()
    date_time = now.strftime("%d-%m-%Y,%H:%M:%S")
    return str(date_time)



if __name__ == "__main__":
    main()
