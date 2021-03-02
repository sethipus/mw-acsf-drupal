This directory should contain automated tests, organized into subdirectories according to testing tool.

Please see [BLT documentation](http://blt.readthedocs.io/en/latest/readme/testing) for more information.

___________________________________________

                    LOCAL ENVIRONMENT CONFIGURATION FOR BEHAT TESTING

1) Install chrome (https://itsfoss.com/install-chrome-ubuntu/#install-chrome-terminal):

        wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb

        sudo dpkg -i google-chrome-stable_current_amd64.deb

2) Run chrome headless with --disable-dev-shm-usage option:

        sudo google-chrome --disable-gpu --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --no-sandbox --disable-dev-shm-usage

3) If you meet chrome installation [errors](https://askubuntu.com/questions/950651/google-chrome-stable-depends-libappindicator1-but-it-is-not-going-to-be-insta)
    then run these commands before installation:
    
        sudo apt-get -f install
        
        sudo apt-get update
    
        sudo apt-get autoremove
        
        sudo apt-get dist-upgrade

4) If you meet this ERROR when trying to run chrome:
"dpkg: error processing package google-chrome-stable (--install):
 dependency problems - leaving unconfigured"

    Then the SOLUTION is: 
    
    You can fix this by installing missing dependencies.
    Just run the following command (after you have run sudo dpkg -i google-chrome-stable_current_i386.deb):

        sudo apt-get install -f
___________________________________________

                    SOME USEFUL COMMANDS

1) How to get docker id from within container:

        head -1 /proc/self/cgroup|cut -d/ -f3

2) Run tests locally from docker container:

        blt tests:behat:run -D behat.paths=Content.feature --no-interaction --environment ci

        blt tests:behat:run -D behat.paths=Base.feature --no-interaction --environment ci

        (RUN ALL TESTS) blt tests:behat:run --no-interaction --environment ci

4) Copy screenshot from docker to windows:

        docker cp 5d61:/tmp/behat_screenshot.jpg C:\behat_screenshots
        
    (5d61 - first 4 characters of mars-web docker id, can be found by typing 'docker ps' from windows)

5) Full update of the project:
    
        (run from outside docker container) ddev composer install  
    
        ddev start
        
        ddev ssh
    
        ddev blt drupal:update 
    
        cd themes/custom/emulsifymars
    
        npm install
    
        npm run build
    
        (return back into /docroot folder) ddev drush cr
 
    This will fix php dependencies, update app, fix npm dependencies, build theme assets, clear caches.
