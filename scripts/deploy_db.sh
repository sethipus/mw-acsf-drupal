#!/bin/sh
## Initiate a code and database update from Acquia Cloud Site Factory
## Origin: http://docs.acquia.com/site-factory/extend/api/examples

# This script should primarily be used on non-production environments.

# Mandatory parameters:
# env : environment to run update on. Example: dev, pprod, qa2, test.
#       - the api user must exist on this environment.
#       - for security reasons, update of prod environment is *not*
#         supported and must be performed manually through UI
# branch : branch/tag to update. Example: qa-build
# update_type : code or code,db

# source $(dirname "$0")/includes/global-api-settings.inc.sh

env="$1"
branch="$2"
update_type="$3"
user="$4"
api_key="$5"
stack_id="$6"

# add comma to "code,db" if not already entered
if [ "$update_type" = "code,db" ]
then
update_type="code, db"
fi

# Edit the following line, replacing [domain] with the appropriate
# part of your domain name.

curl "https://www.${env}-mars.acsitefactory.com/api/v1/update" \
-f -v -u ${user}:${api_key} -k -X POST \
-H 'Content-Type: application/json' \
-d "{\"stack_id\": ${stack_id}, \"sites_ref\": \"${branch}\", \"sites_type\": \"${update_type}\"}"
