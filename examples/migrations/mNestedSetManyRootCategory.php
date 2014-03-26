<?php

use yii\db\Schema;

class mNestedSetManyRootCategory extends \yii\db\Migration
{
    public function up()
    {
    	$this->createTable('{{%category}}', [
			'id'	=>	Schema::TYPE_PK,
    		'root'	=>	Schema::TYPE_INTEGER,
    		'lft'	=>	Schema::TYPE_INTEGER,
    		'rgt'	=>	Schema::TYPE_INTEGER,
    		'level'	=>	Schema::TYPE_INTEGER,
    		'title'	=>	Schema::TYPE_STRING,
    		'description'	=>	Schema::TYPE_TEXT,
    	]);

    }

    public function down()
    {
    	$this->dropTable('{{%category}}');
    }
}
