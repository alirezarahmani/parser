# parser

## asumtions:

- in righ side of each operations there is no filed there is only number or string. for example eventName = purchase purchase is string.
- it convert to https://www.elastic.co/guide/en/kibana/current/kuery-query.html language.

## How to run:
- composer install 
- php -f ./src/index.php

## Sample Output:

``  {  eventname: = purchase  and  eventdata.detail.cpu.detail: { type = 64bit }    and  eventdata.category: { digital = phone }    or  eventdata: { price < 600000 }    } ``
