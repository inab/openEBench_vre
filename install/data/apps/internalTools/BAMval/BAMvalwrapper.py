#!/usr/bin/python2.7

import os
import sys
import argparse
import json
import time
import socket # print localhost
import logging
import re
import pprint
import multiprocessing
#import psutil  # available memory
import subprocess
import shutil
import glob
import tarfile
import subprocess
import random

out_dir=""

class Mugparams(object):
	@staticmethod
	def check_json(json_file):
		logger = logging.getLogger("lg")
		if not os.path.exists(json_file):
			raise argparse.ArgumentTypeError("%s does not exist" % json_file)

		with open(json_file,'r') as file_data:    
			try:
				data = json.load(file_data)
			except  ValueError, e:
				logger.exception("%s in not a valid json file." % json_file)
			
		return data

	@staticmethod
	def readable_dir(d):
		if not os.path.isdir(d):
			raise Exception("readable_dir:{0} is not a directory path or is not accessible".format(d))
		if os.access(d, os.R_OK):
			return d
		else:
			raise Exception("readable_dir:{0} is not a readable dir".format(d))


	@staticmethod
	def writeable_file(f):
		if not os.path.isfile(f):
			d =  os.path.dirname(f)
			# TODO Fails if relative path given
			if not os.path.isdir(d):
				raise Exception("writeable_file:{0} not in a existing directory path, or not accessible".format(d))
			else:
				if os.access(d, os.W_OK):
					return f
				else:
					raise Exception("writeable_file:{0} is not a writeable dir".format(d))
		else:
			return f

	@staticmethod
	def process_arguments(args):
		global out_dir
		logger = logging.getLogger("lg")

		# Setting working directory (project)
		proj_idx = next(idx for (idx, d) in enumerate(args.config["arguments"]) if d["name"] == "project")
		out_dir = args.root_dir+"/"+args.config["arguments"][proj_idx]["value"]

		logger.info("Output file directory set to %s" % out_dir)
		
		# Indexing config arguments by name
    		arguments_by_name = dict((d["name"], d["value"]) for (index, d) in enumerate(args.config["arguments"]))
		args.config["arguments"] = arguments_by_name

		# Indexing config input_files by name (name could not be unique - because of allow_multiple)
    		inputs_by_name = {}
		for index,d in enumerate(args.config["input_files"]):
			name = args.config["input_files"][index]["name"]
			if name in inputs_by_name:
				pprint.pprint(inputs_by_name[name])
				if type(inputs_by_name[name] is str):
					prev = inputs_by_name[name]
					inputs_by_name[name]= list()
					inputs_by_name[name].append(prev)
				inputs_by_name[name].append(d["value"])
			else:
				inputs_by_name[name]=d["value"]
		args.config["input_files"] = inputs_by_name
		logger.debug("Configuration file arguments and input_files are:\n %s " % pprint.pformat(args.config))
		return 1

	@staticmethod
	def process_metadata(args):
		global out_dir
		logger = logging.getLogger("lg")

		# Indexing metadata files by file_id ([_id])
    		metadata_by_id = dict((d["_id"], dict(d)) for (index, d) in enumerate(args.metadata))
		args.metadata = metadata_by_id
		logger.debug("VRE metadata for input_files is:\n %s " % pprint.pformat(args.metadata))
	
		return 1



#
# Executing pipeline
# Calling MuG_Chromatin_equ_structure and MuG_Chromatin_sampling software in a subprocess

def run_pipeline(args, num_cores, x_rnd):

	sort = args.config["input_files"]["sequence"]
	sequence = args.root_dir + "/" + args.metadata[sequence_file_id]["file_path"]
	nucl_pos_file_id   = args.config["input_files"]["nuclPos"]
	nucl_pos = args.root_dir + "/" + args.metadata[nucl_pos_file_id]["file_path"]

        tmp_dir = "{0}/{1}/str_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
	bashCommand = "cd /home/MuG/MuG_Chromatin_equ_structure/src_test; bash run.sh %s %s %s" % (nucl_pos, sequence, tmp_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout

		usr_dir = args.root_dir + "/" + args.config["arguments"]["project"]
		bashCommand = "cp %s/output/chromdyn_str.pdb %s" % (tmp_dir, usr_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout

	if "createTrajectory" in args.config["arguments"]["operations"]:
		print "do Trajectory"
		sequence_file_id   = args.config["input_files"]["sequence"]
                sequence = args.root_dir + "/" + args.metadata[sequence_file_id]["file_path"]
		nucl_pos_file_id   = args.config["input_files"]["nuclPos"]
                nucl_pos = args.root_dir + "/" + args.metadata[nucl_pos_file_id]["file_path"]

		tmp_dir = "{0}/{1}/tra_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
		iterations = args.config["arguments"]["createTrajectory:numStruct"]
		bashCommand = "cd /home/MuG/MuG_Chromatin_sampling/src_test; bash run.sh %s %s %s %s" % (nucl_pos, sequence, iterations, tmp_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
                proc_stdout = process.communicate()[0].strip()
                print proc_stdout

		usr_dir = args.root_dir + "/" + args.config["arguments"]["project"]
		bashCommand = "cp %s/output/chromdyn_start_str.pdb %s/output/chromdyn_str.dcd %s/output/chromdyn_dummy_str.pdb %s" % (tmp_dir, tmp_dir, tmp_dir, usr_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
                proc_stdout = process.communicate()[0].strip()
                print proc_stdout


	if "create3DfromNucleaR" in args.config["arguments"]["operations"]:
		print "do 3D from NucleaR"
		gff_file_id   = args.config["input_files"]["gffNucleaR"]
		gff_file = args.root_dir + "/" + args.metadata[gff_file_id]["file_path"]
		
		gen_reg   = args.config["arguments"]["create3DfromNucleaR:genRegion"]

                tmp_dir = "{0}/{1}/str_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
		bashCommand = " mkdir %s" % tmp_dir
		print bashCommand
		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout

		assembly = args.metadata[gff_file_id]["meta_data"]["assembly"]

		genome_file = "{0}/refGenomes/{1}/{1}.fa".format(args.public_dir,assembly)

		bashCommand = " /home/MuG/MuG_Chromatin_equ_structure/src_test/nucleR2structure.py --calls %s --genome_file %s --range %s --seq_output %s --nucs_output %s --margin 4" % (gff_file, genome_file, gen_reg, tmp_dir +"/nucleR_to_3D_seq.txt", tmp_dir +"/nucleR_to_3D_nucl_pos.txt")
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout
	
		bashCommand = "cd /home/MuG/MuG_Chromatin_equ_structure/src_test; bash run.sh %s %s %s" % (tmp_dir +"/nucleR_to_3D_nucl_pos.txt", tmp_dir +"/nucleR_to_3D_seq.txt", tmp_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout

		usr_dir = args.root_dir + "/" + args.config["arguments"]["project"]
		bashCommand = "cp %s/output/chromdyn_str.pdb %s" % (tmp_dir, usr_dir)
		print bashCommand

		process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
		proc_stdout = process.communicate()[0].strip()
       		print proc_stdout


	return 1

#
# Prepare metadata for the output files 

def prepare_results(args, x_rnd):
	
	global out_dir
	logger  = logging.getLogger("lg")

	if (args.out_metadata):

		# Create out_metadata JSON 
		json_data  = {}
		json_data['output_files']= []


		if ("createStructure" in args.config["arguments"]["operations"]) or ("create3DfromNucleaR" in args.config["arguments"]["operations"]):
			### PDB_chromatin_structure
			pdbMeta = {}
	
			# Set name
			# Should coincide with tool.json
			pdbMeta["name"]      = "PDB_chromatin_structure"
	
			# Set file_path
			# Absolute path. Should be better relative to root_dir?
			tmp_dir  = args.root_dir + "/" + args.config["arguments"]["project"]
			pdb_file = tmp_dir + "/chromdyn_str.pdb"
			pdbMeta["file_path"] = pdb_file
	
			# Set source_id & taxon_id
			pdbMeta["source_id"] = []
			if "sequence" in args.config['input_files']:
				pdbMeta["source_id"].append(args.config['input_files']["sequence"])
	
			# Set taxon_id
			# taxon_id is inherited from the input file (i.e the source_id)
			pdbMeta["taxon_id"]  = 0
			if pdbMeta["source_id"]:
				for file_id in pdbMeta["source_id"]:
					pprint.pprint(args.metadata)
					if args.metadata[file_id]["taxon_id"]:
						pdbMeta["taxon_id"] = args.metadata[file_id]["taxon_id"]
						break
			
					
			# Append output_file metadata into JSON data
			json_data['output_files'].append(pdbMeta)
			

		

		if "createTrajectory" in args.config["arguments"]["operations"]:

			### chromatin_starting_trajectory_structure
	                pdbMeta1 = {}
	
	                # Set name. Should coincide with tool.json
	                pdbMeta1["name"]      = "PDB_chromatin_starting_structure"
	
	                # Set file_path. Absolute path. Should be better relative to root_dir?
	                tmp_dir = args.root_dir + "/" + args.config["arguments"]["project"]
	                pdb_file = tmp_dir + "/chromdyn_start_str.pdb"
	                pdbMeta1["file_path"] = pdb_file
	
	                # Set source_id & taxon_id
	                pdbMeta1["source_id"] = []
	                if "sequence" in args.config['input_files']:
	                        pdbMeta1["source_id"].append(args.config['input_files']["sequence"])
	
	                # Set taxon_id. taxon_id is inherited from the input file (i.e the source_id)
	                pdbMeta1["taxon_id"]  = 0
	                if pdbMeta1["source_id"]:
	                        for file_id in pdbMeta1["source_id"]:
	                                pprint.pprint(args.metadata)
	                                if args.metadata[file_id]["taxon_id"]:
	                                        pdbMeta1["taxon_id"] = args.metadata[file_id]["taxon_id"]
	                                        break
	
	                # Append output_file metadata into JSON data
	                json_data['output_files'].append(pdbMeta1)
	


			### chromatin_dummy_trajectory_structure
			pdbMeta2 = {}
	
			# Set name. Should coincide with tool.json
			pdbMeta2["name"]      = "PDB_dummy_chromatin_structure"
	
			# Set file_path. Absolute path. Should be better relative to root_dir?
			tmp_dir = args.root_dir + "/" + args.config["arguments"]["project"]
			pdb_file = tmp_dir + "/chromdyn_dummy_str.pdb"
			pdbMeta1["file_path"] = pdb_file
	
			# Set source_id & taxon_id
			pdbMeta2["source_id"] = []
			if "sequence" in args.config['input_files']:
				pdbMeta2["source_id"].append(args.config['input_files']["sequence"])
	
			# Set taxon_id. taxon_id is inherited from the input file (i.e the source_id)
			pdbMeta2["taxon_id"]  = 0
			if pdbMeta2["source_id"]:
				for file_id in pdbMeta2["source_id"]:
					pprint.pprint(args.metadata)
					if args.metadata[file_id]["taxon_id"]:
						pdbMeta2["taxon_id"] = args.metadata[file_id]["taxon_id"]
						break
			
			# Append output_file metadata into JSON data
			json_data['output_files'].append(pdbMeta2)
				

	
			### chromatin_trajectory
			trajMeta = {}
	
			# Set name
			# Should coincide with tool.json
			trajMeta["name"]      = "chromatin_trajectory"
	
			# Set file_path
			# Absolute path. Should be better relative to root_dir?
			tmp_dir = args.root_dir + "/" + args.config["arguments"]["project"]
			traj_file = tmp_dir + "/chromdyn_str.dcd"
			trajMeta["file_path"] = traj_file
	
			# Set source_id & taxon_id
			trajMeta["source_id"] = []
			if "sequence" in args.config['input_files']:
				trajMeta["source_id"].append(args.config['input_files']["sequence"])
	
			# Set taxon_id. taxon_id is inherited from the input file (i.e the source_id)
			trajMeta["taxon_id"]  = 0
			if trajMeta["source_id"]:
				for file_id in trajMeta["source_id"]:
					pprint.pprint(args.metadata)
					if args.metadata[file_id]["taxon_id"]:
						trajMeta["taxon_id"] = args.metadata[file_id]["taxon_id"]
						break
					
			# Append output_file metadata into JSON data
			json_data['output_files'].append(trajMeta)
				
	
	

		# Prepare last output file: TAR of outputs, *CSVs and *PNGs
		files      = []
		extensions = ('*.txt','*.csv','*.png')
		out_dirs = []
		if "createStructure" in args.config["arguments"]["operations"]:
			out_dirs.append("{0}/{1}/str_{2}/output".format(args.root_dir, args.config["arguments"]["project"], x_rnd))
		if "createTrajectory" in args.config["arguments"]["operations"]:
			out_dirs.append("{0}/{1}/tra_{2}/output".format(args.root_dir, args.config["arguments"]["project"], x_rnd))
		if "create3DfromNucleaR" in args.config["arguments"]["operations"]:
			out_dirs.append("{0}/{1}/str_{2}/output".format(args.root_dir, args.config["arguments"]["project"], x_rnd))

		for out_dir in out_dirs:
			for extension in extensions:
				files.extend(glob.glob(out_dir+"/"+extension))	

		tmp_dir = args.root_dir + "/" + args.config["arguments"]["project"]
		out_tar    =  tmp_dir + "/results.tar.gz" 

		tar = tarfile.open(out_tar, "w:gz")
		
		for fil in files:
			logger.info ("Packing %s into statistics TAR" % os.path.basename(fil)) 
			tar.add(fil, arcname=os.path.basename(fil))
		tar.close()

		# Set metadata required for TAR output file
		result = {}
		result["name"]      = "summary"
		result["source_id"] = []
		if "sequence" in args.config['input_files']:
			result["source_id"].append(args.config['input_files']["sequence"])
		result["file_path"] = out_tar
		result["taxon_id"] = 0
		json_data['output_files'].append(result)



		# Write down output file metadata
		J = open(args.out_metadata, 'wb')
		json.dump(json_data,J, indent=4)
		J.close
		logger.info("Output files annotated into %s" % args.out_metadata)

		
		# Delete temporary directory
		if "createStructure" in args.config["arguments"]["operations"]:
			tmp_dir = "{0}/{1}/str_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
			bashCommand = "rm -r %s" % tmp_dir
			print bashCommand
	
			process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
			proc_stdout = process.communicate()[0].strip()
	       		print proc_stdout


		if "create3DfromNucleaR" in args.config["arguments"]["operations"]:
			tmp_dir = "{0}/{1}/str_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
			bashCommand = "rm -r %s" % tmp_dir
			print bashCommand
	
			process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
			proc_stdout = process.communicate()[0].strip()
	       		print proc_stdout



		if "createTrajectory" in args.config["arguments"]["operations"]:
			tmp_dir = "{0}/{1}/tra_{2}".format(args.root_dir, args.config["arguments"]["project"], x_rnd)
			bashCommand = "rm -r %s" % tmp_dir
			print bashCommand
	
			process = subprocess.Popen(bashCommand,stdout=subprocess.PIPE, shell=True)
			proc_stdout = process.communicate()[0].strip()
	       		print proc_stdout


def main():

	# Start logging

	logger = logging.getLogger("lg")
	logger.setLevel(logging.INFO)
	formatter = logging.Formatter(fmt='%(asctime)s - %(module)s - %(levelname)s - %(message)s')

	handler = logging.FileHandler('%s.log' %  os.path.splitext(os.path.basename(__file__))[0])
	handler.setLevel(logging.INFO)
	handler.setFormatter(formatter)
	logger.addHandler(handler)

	streamhandler = logging.StreamHandler()
	streamhandler.setLevel(logging.INFO)
	streamhandler.setFormatter(formatter)
	logger.addHandler(streamhandler)

	logger.info('Starting %s' % __file__)

	# Parse CMD

	parser = argparse.ArgumentParser(prog="chromatindyn_wf",  description="Chromatin Dynamics workflow")
		
	parser.add_argument("--config",  required=True,  type=Mugparams.check_json, metavar="CONFIG_JSON",
				help="JSON file containing workflow parameters")
	parser.add_argument("--root_dir",  required=True,  type=Mugparams.readable_dir, metavar="ABS_PATH",
				help="Absolute path of the user data directory.")
	parser.add_argument("--public_dir",  required=False,  type=Mugparams.readable_dir, metavar="PUBLIC_PATH",
				help="Absolute path of the MuG public directory (with reference genome data, etc).")
	parser.add_argument("--metadata",  required=True,  type=Mugparams.check_json, metavar="METADATA_JSON",
				help="JSON file containing MuG metadata files")
	parser.add_argument("--out_metadata",  required=False,  type=Mugparams.writeable_file, metavar="RESULTS_JSON",
				help="JSON file containing results metadata")
	parser.add_argument("-v", "--verbose", required=False, action="store_true", 
				help="increase output verbosity")
	parser.add_argument('--version', action='version', version='%(prog)s 0.1')

	args = parser.parse_args()

	if args.verbose:
		logger.setLevel(logging.DEBUG)
		handler.setLevel(logging.DEBUG)
		handler.setLevel(logging.DEBUG)
		logger.addHandler(handler)
		streamhandler.setLevel(logging.DEBUG)
		logger.addHandler(streamhandler)
		logger.debug("Verbose mode on")

	# Parse config
	Mugparams.process_arguments(args)
	Mugparams.process_metadata(args)


	# Print host info
	
	num_cores = multiprocessing.cpu_count()
	host      = socket.gethostname()
	#mem      = psutil.virtual_memory()
	logger.debug('HOST=%s CPUs=%s MEM=x' %(host,num_cores)) 


	# Run pipeline
	x_rnd = int(random.random()*10000000)
	outfiles = run_pipeline(args, num_cores, x_rnd)

	# Results 

	prepare_results(args, x_rnd)

if __name__ == '__main__':
	main()
