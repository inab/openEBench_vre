#!/bin/bash

VERSION=0.1.0
SUBJECT=wget
USAGE="USAGE: $SUBJECT --url URL --output OUTNAME --working_dir OUTDIR"

# --- Option processing --------------------------------------------


while [[ $# -gt 1 ]]
do
key="$1"
case $key in
    --url)
    Url="$2"
    shift
    ;;
    --output)
    Outname="$2"
    shift
    ;;
    --working_dir)
    Tmpdir="$2"
    shift
    ;;
    *)
    # unknown option
    ;;
esac
shift # past argument or value
done

Tmpdir=${Tmpdir:-$(pwd)}

cd $Tmpdir
wget $Url -O $Outname
