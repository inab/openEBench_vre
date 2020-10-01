#!/bin/bash
 
# print it 
toolJSON="$1"
validator="$2"
cd "../../../oeb_tool_validation/fairtracks_validator/python"
source ".py3env/bin/activate"
exec python3.6 fairGTrackJsonValidate.py OpEB-VRE-schemas/"$validator" "${toolJSON}"

