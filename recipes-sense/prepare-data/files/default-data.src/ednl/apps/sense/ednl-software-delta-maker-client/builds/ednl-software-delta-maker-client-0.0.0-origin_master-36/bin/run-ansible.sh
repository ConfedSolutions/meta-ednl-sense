TAG="devupdate-$1"
LOCATION='/home/app/sense/ednl-software-delta-maker-client/current/ansible'
FILENAME=$2
DEVPARAMETERS="${@:3}"
#echo ansible-playbook -i ${LOCATION}/hosts ${LOCATION}/local.yml -t ${TAG} --extra-vars "filename=${FILENAME} ${@:3}"
echo ansible-playbook -i ${LOCATION}/hosts ${LOCATION}/local.yml -t ${TAG} --extra-vars "filename=${FILENAME} ${DEVPARAMETERS}"
ansible-playbook -i ${LOCATION}/hosts ${LOCATION}/local.yml -t ${TAG} --extra-vars "filename=${FILENAME} ${DEVPARAMETERS}"
