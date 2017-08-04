# Nested Sets Behavior for Yii 2

[![Build Status](https://img.shields.io/travis/creocoder/yii2-nested-sets/master.svg?style=flat-square)](https://travis-ci.org/creocoder/yii2-nested-sets)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/creocoder/yii2-nested-sets/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/creocoder/yii2-nested-sets/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/creocoder/yii2-nested-sets.svg?style=flat-square)](https://packagist.org/packages/creocoder/yii2-nested-sets)
[![Total Downloads](https://img.shields.io/packagist/dt/creocoder/yii2-nested-sets.svg?style=flat-square)](https://packagist.org/packages/creocoder/yii2-nested-sets)

A modern nested sets behavior for the Yii framework utilizing the Modified Preorder Tree Traversal algorithm.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require creocoder/yii2-nested-sets
```

or add

```
"creocoder/yii2-nested-sets": "0.9.*"
```

to the `require` section of your `composer.json` file.

## Migrations

Run the following command

```bash
$ yii migrate/create create_menu_table
```

Open the `/path/to/migrations/m_xxxxxx_xxxxxx_create_menu_table.php` file,
inside the `up()` method add the following

```php
$this->createTable('{{%menu}}', [
    'id' => $this->primaryKey(),
    //'tree' => $this->integer()->notNull(),
    'lft' => $this->integer()->notNull(),
    'rgt' => $this->integer()->notNull(),
    'depth' => $this->integer()->notNull(),
    'name' => $this->string()->notNull(),
]);
```

To use multiple tree mode uncomment `tree` field.

## Configuring

Configure model as follows

```php
use creocoder\nestedsets\NestedSetsBehavior;

class Menu extends \yii\db\ActiveRecord
{
    public function behaviors() {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                // 'treeAttribute' => 'tree',
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find()
    {
        return new MenuQuery(get_called_class());
    }
}
```

To use multiple tree mode uncomment `treeAttribute` array key inside `behaviors()` method.

Configure query class as follows

```php
use creocoder\nestedsets\NestedSetsQueryBehavior;

class MenuQuery extends \yii\db\ActiveQuery
{
    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
    
    public function where($condition) {
        // Do not allow to break behavior magic
        return parent::andWhere($condition);
    }
    
}
```

## Usage

### Making a root node

To make a root node

```php
$countries = new Menu(['name' => 'Countries']);
$countries->makeRoot();
```

The tree will look like this

```
- Countries
```

### Prepending a node as the first child of another node

To prepend a node as the first child of another node

```php
$russia = new Menu(['name' => 'Russia']);
$russia->prependTo($countries);
```

The tree will look like this

```
- Countries
    - Russia
```

### Appending a node as the last child of another node

To append a node as the last child of another node

```php
$australia = new Menu(['name' => 'Australia']);
$australia->appendTo($countries);
```

The tree will look like this

```
- Countries
    - Russia
    - Australia
```

### Inserting a node before another node

To insert a node before another node

```php
$newZeeland = new Menu(['name' => 'New Zeeland']);
$newZeeland->insertBefore($australia);
```

The tree will look like this

```
- Countries
    - Russia
    - New Zeeland
    - Australia
```

### Inserting a node after another node

To insert a node after another node

```php
$unitedStates = new Menu(['name' => 'United States']);
$unitedStates->insertAfter($australia);
```

The tree will look like this
```
- Countries
    - Russia
    - New Zeeland
    - Australia
    - United States
```

### Getting the root nodes

To get all the root nodes

```php
$roots = Menu::find()->roots()->all();
```

### Getting the leaves nodes

To get all the leaves nodes

```php
$leaves = Menu::find()->leaves()->all();
```

To get all the leaves of a node

```php
$countries = Menu::findOne(['name' => 'Countries']);
$leaves = $countries->leaves()->all();
```

### Getting children of a node

To get all the children of a node

```php
$countries = Menu::findOne(['name' => 'Countries']);
$children = $countries->children()->all();
```

To get the first level children of a node

```php
$countries = Menu::findOne(['name' => 'Countries']);
$children = $countries->children(1)->all();
```

### Getting parents of a node

To get all the parents of a node

```php
$countries = Menu::findOne(['name' => 'Countries']);
$parents = $countries->parents()->all();
```

To get the first parent of a node

```php
$countries = Menu::findOne(['name' => 'Countries']);
$parent = $countries->parents(1)->one();
```

## Donating

Support this project and [others by creocoder](https://gratipay.com/creocoder/) via [gratipay](https://gratipay.com/creocoder/).

[![Support via Gratipay](https://cdn.rawgit.com/gratipay/gratipay-badge/2.3.0/dist/gratipay.svg)](https://gratipay.com/creocoder/)
