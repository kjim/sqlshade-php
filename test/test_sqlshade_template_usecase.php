<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Template.php');

$t = new lime_test();

// @setup
$plainQuery = "
    SELECT
        t_favorite.id
        , t_favorite.owned_userid
        , t_favorite.remarks
        , (CASE
           WHEN t_favorite.updated_at IS NOT NULL THEN t_favorite.updated_at
           ELSE t_favorite.created_at
           END
           ) AS last_updated_at
        /*#if join_self_favorite_data*/
        , (CASE
           WHEN self_bookmarked.owned_userid IS NULL THEN 0 -- FALSE
           ELSE 1 -- TRUE
           END
           ) AS self_favorite_data
        /*#endif*/
    FROM
        t_favorite
        /*#if join_self_favorite_data*/
        LEFT OUTER JOIN (
            SELECT DISTINCT
                t_favorite_item.owned_userid
                , t_favorite_item.reference_id
            FROM
                t_favorite_item
                INNER JOIN t_member
                  on (t_member.id = t_favorite_item.owned_userid)
            WHERE TRUE
                AND t_member.id = /*:self_userid*/10
                AND t_member.status = /*:status_activated*/1
        ) as self_bookmarked
            on (self_bookmarked.reference_id = t_favorite.id)
        /*#endif*/
    WHERE
        TRUE
        AND (t_favorite.id IN /*:favorite_ids*/(2, 3, 4))
        AND (t_favorite.status = /*:status_activated*/1)
    ;
";
$template = new SQLShade_Template($plainQuery);

// @test
$parameters = array(
    'join_self_favorite_data' => false,
    'favorite_ids' => array(1, 3245, 3857),
    'status_activated' => 1,
    );

list($query, $bound) = $template->render($parameters);
$t->unlike($query, '/AS self_favorite_data/', 'disable join_self_favorite_data block: AS self_favorite_data');
$t->unlike($query, '/LEFT OUTER JOIN/', 'disable join_self_favorite_data block: LEFT OUTER JOIN');
$t->is_deeply($bound, array(1, 3245, 3857, 1), 'bound correct variables');

// @test
$parameters = array(
    'join_self_favorite_data'=> true,
    'self_userid' => 3586,
    'favorite_ids' => array(11, 3245, 3857),
    'status_activated' => 1,
    );

list($query, $bound) = $template->render($parameters);
$t->like($query, '/AS self_favorite_data/', 'enable join_self_favorite_data block: AS self_favorite_data');
$t->like($query, '/LEFT OUTER JOIN/', 'enable join_self_favorite_data block: LEFT OUTER JOIN');
$t->is_deeply($bound, array(3586, 1, 11, 3245, 3857, 1), 'bound correct variables');


// @setup
$exectableWhereClauseQuery = "
    /*#if false*/
    SELECT * FROM t_favorite WHERE TRUE
    /*#endif*/
        /*#if use_condition_keyword*/
        AND (FALSE
            /*#for keyword in keywords*/
            OR UPPER(t_favorite.remarks) LIKE UPPER('%' || /*:keyword*/'' || '%')
            /*#endfor*/
        )
        /*#endif*/
        /*#if use_condition_fetch_status*/
        AND t_favorite.status IN /*:fetch_status*/(1, 100)
        /*#endif*/
        /*#if use_condition_sector*/
        AND t_favorite.record_type EXISTS (
            SELECT 1 FROM /*#embed sector_table*/t_sector_AA/*#endembed*/
        )
        /*#endif*/
        AND t_favorite.status = /*:status_activated*/1
    /*#if false*/
    ;
    /*#endif*/
";

// @test
$tempalteWhereClause = new SQLShade_Template($exectableWhereClauseQuery, array('strict' => false));
list($tmpQuery, $_) = $tempalteWhereClause->render(array('false' => false));
