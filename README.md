# SQLShade is a template system for SQL

SQLShade is Inspired by the *2-Way SQL* idea and extended it(originated by Seasar project’s S2Dao).
This project is an another implementation it, And a big purpose as **Write Executable SQL into programming code**.


## Quick Start

### A Simple Example

    $plainQuery = "SELECT * FROM t_member
        WHERE TRUE
            AND t_member.feature_id IN /*:feature_id*/(100, 200)
            AND t_member.nickname LIKE /*:nickname*/'%kjim%'
            AND t_member.sex IN /*:sex*/('male', 'female')";

    $template = new SQLShade_Template($plainQuery);

    list($query, $bound) = $template->render(array(
        'feature_id' => array(3845, 295, 1, 637, 221, 357),
        'nickname' => '%keiji%',
        'sex' => array('male', 'female', 'other'),
        ));

    print_r($query);
    #> SELECT * FROM t_member
    #> WHERE TRUE
    #>     AND t_member.feature_id IN (?, ?, ?, ?, ?, ?)
    #>     AND t_member.nickname LIKE ?
    #>     AND t_member.sex IN (?, ?, ?)

    print_r($bound);
    #> Array
    #> (
    #>     [0] => 3845
    #>     [1] => 295
    #>     [2] => 1
    #>     [3] => 637
    #>     [4] => 221
    #>     [5] => 357
    #>     [6] => %keiji%
    #>     [7] => male
    #>     [8] => female
    #>     [9] => other
    #> )


## Run Tests

    % git clone git://github.com/kjim/sqlshade-php.git
    % cd sqlshade-php
    % ./requirements.sh
    % $PHP_HOME/bin/php tests.php


## Currently Supported PHP Versions

* PHP 5.2.13+
