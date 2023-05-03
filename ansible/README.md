Ansible
======================
This playbook installs the calendar-kiosk.


This assume some familiarity with the Ansible configuration management system and that you have an ansible control machine configured.  Before running the playbook, you must first have a tarball of the calendar-kiosk that was previously built.  There is a Makefile in the project root.

Dependencies
-------------
This playbook requires a few roles.
You can probably use ansible-galaxy to download them:

    ansible-galaxy install --roles-path ./roles -r roles.yml

or for development:

```
git clone https://github.com/City-of-Bloomington/ansible-role-linux.git  ./roles/City-of-Bloomington.linux
git clone https://github.com/City-of-Bloomington/ansible-role-apache.git ./roles/City-of-Bloomington.apache
git clone https://github.com/City-of-Bloomington/ansible-role-mysql.git  ./roles/City-of-Bloomington.mysql
git clone https://github.com/City-of-Bloomington/ansible-role-php.git    ./roles/City-of-Bloomington.php
etc
```

Variables
--------------
You'll need to set these variables somewhere in your inventory.

```yml
calendar_kiosk_archive_path: "../build/calendar-kiosk.tar.gz"
calendar_kiosk_install_path: "/srv/sites/calendar-kiosk"
calendar_kiosk_site_home:    "/srv/data/calendar-kiosk"

calendar_kiosk_base_uri: "/calendar-kiosk"
calendar_kiosk_base_url: "https://{{ ansible_host }}{{ calendar_kiosk_base_uri }}"

calendar_kiosk_google:
  user:        'someone@example.com'
  calendar_id: 'xxxxxx@calendar.google.com'

calendar_kiosk_location_map: |
  '|^.+Atrium.+$|'                                      => '401 N Morton ST',
  '|^.+Council Chambers.+$|'                            => '401 N Morton ST',
  '|^.+Kelly Conference Room.+$|'                       => '401 N Morton ST',
  '|^.+Lemon Conference Room.+$|'                       => '401 N Morton ST',
  '|^.+McCloskey Conference Room.+$|'                   => '401 N Morton ST',
```

Run the Playbook
-----------------

    ansible-playbook deploy.yml -i /path/to/inventory --limit=host.example.com

License
-------

Copyright (c) 2023 City of Bloomington, Indiana

This material is avialable under the GNU Affero General Public License (GLP):
https://www.gnu.org/licenses/agpl-3.0.txt


