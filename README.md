Nested Set behavior 2 (preview version)
=======================================

This extension allows managing trees stored in database as nested sets.
It's implemented as Active Record behavior.

Installing and configuring
--------------------------

First you need to configure model as follows:

```php
public function behaviors()
{
    return array(
        'nestedSetBehavior'=>array(
            'class'=>'NestedSet',
            'leftAttribute'=>'lft',
            'rightAttribute'=>'rgt',
            'levelAttribute'=>'level',
        ),
    );
}
```

There is no need to validate fields specified in `leftAttribute`,
`rightAttribute`, `rootAttribute` and `levelAttribute` options. Moreover,
there could be problems if there are validation rules for these. Please
check if there are no rules for fields mentioned in model's rules() method.

In case of storing a single tree per database, DB structure can be built with
`extensions/yiiext/behaviors/trees/schema.sql`. If you're going to store multiple
trees you'll need `extensions/yiiext/behaviors/trees/schema_many_roots.sql`.

By default `leftAttribute`, `rightAttribute` and `levelAttribute` values are
matching field names in default DB schemas so you can skip configuring these.

There are two ways this behavior can work: one tree per table and multiple trees
per table. The mode is selected based on the value of `hasManyRoots` option that
is `false` by default meaning single tree mode. In multiple trees mode you can
set `rootAttribute` option to match existing field in the table storing the tree.

Selecting from a tree
---------------------

In the following we'll use an example model `Category` with the following in its
DB:

~~~
- 1. Mobile phones
	- 2. iPhone
	- 3. Samsung
		- 4. X100
		- 5. C200
	- 6. Motorola
- 7. Cars
	- 8. Audi
	- 9. Ford
	- 10. Mercedes
~~~

In this example we have two trees. Tree roots are ones with ID=1 and ID=7.

### Getting all roots

Using `NestedSetBehavior::roots()`:

```php
$roots=Category::model()->roots()->findAll();
```

Result:

Array of Active Record objects corresponding to Mobile phones and Cars nodes.

### Getting all descendants of a node

Using `NestedSetBehavior::descendants()`:

```php
$category=Category::model()->findByPk(1);
$descendants=$category->descendants()->findAll();
```

Result:

Array of Active Record objects corresponding to iPhone, Samsung, X100, C200 and Motorola.

### Getting all children of a node

Using `NestedSetBehavior::children()`:

```php
$category=Category::model()->findByPk(1);
$descendants=$category->children()->findAll();
```

Result:

Array of Active Record objects corresponding to iPhone, Samsung and Motorola.

### Getting all ancestors of a node

Using `NestedSetBehavior::ancestors()`:

```php
$category=Category::model()->findByPk(5);
$ancestors=$category->ancestors()->findAll();
```

Result:

Array of Active Record objects corresponding to Samsung and Mobile phones.

### Getting parent of a node

Using `NestedSetBehavior::parent()`:

```php
$category=Category::model()->findByPk(9);
$parent=$category->parent()->find();
```

Result:

Array of Active Record objects corresponding to Cars.

### Getting node siblings

Using `NestedSetBehavior::prev()` or
`NestedSetBehavior::next()`:

```php
$category=Category::model()->findByPk(9);
$nextSibling=$category->next()->find();
```

Result:

Array of Active Record objects corresponding to Mercedes.

### Getting the whole tree

You can get the whole tree using standard AR methods like the following.

For single tree per table:

```php
Category::model()->findAll(array('order'=>'lft'));
```

For multiple trees per table:

```php
Category::model()->findAll(array('condition'=>'root=?','order'=>'lft'),array($root_id));
```

Modifying a tree
----------------

In this section we'll build a tree like the one used in the previous section.

### Creating root nodes

You can create a root node using `NestedSetBehavior::saveNode()`.
In a single tree per table mode you can create only one root node. If you'll attempt
to create more there will be CException thrown.

```php
$root=new Category;
$root->title='Mobile Phones';
$root->saveNode();
$root=new Category;
$root->title='Cars';
$root->saveNode();
```

Result:

~~~
- 1. Mobile Phones
- 2. Cars
~~~

### Adding child nodes

There are multiple methods allowing you adding child nodes. To get more info
about these refer to API. Let's use these
to add nodes to the tree we have:

```php
$category1=new Category;
$category1->title='Ford';
$category2=new Category;
$category2->title='Mercedes';
$category3=new Category;
$category3->title='Audi';
$root=Category::model()->findByPk(1);
$category1->appendTo($root);
$category2->insertAfter($category1);
$category3->insertBefore($category1);
```

Result:

~~~
- 1. Mobile phones
	- 3. Audi
	- 4. Ford
	- 5. Mercedes
- 2. Cars
~~~

Logically the tree above doesn't looks correct. We'll fix it later.

```php
$category1=new Category;
$category1->title='Samsung';
$category2=new Category;
$category2->title='Motorola';
$category3=new Category;
$category3->title='iPhone';
$root=Category::model()->findByPk(2);
$category1->appendTo($root);
$category2->insertAfter($category1);
$category3->prependTo($root);
```

Result:

~~~
- 1. Mobile phones
	- 3. Audi
	- 4. Ford
	- 5. Mercedes
- 2. Cars
	- 6. iPhone
	- 7. Samsung
	- 8. Motorola
~~~

```php
$category1=new Category;
$category1->title='X100';
$category2=new Category;
$category2->title='C200';
$node=Category::model()->findByPk(3);
$category1->appendTo($node);
$category2->prependTo($node);
```

Result:

~~~
- 1. Mobile phones
	- 3. Audi
		- 9. С200
		- 10. X100
	- 4. Ford
	- 5. Mercedes
- 2. Cars
	- 6. iPhone
	- 7. Samsung
	- 8. Motorola
~~~

Modifying a tree
----------------

In this section we'll finally make our tree logical.

### Tree modification methods

There are several methods allowing you to modify a tree. To get more info
about these refer to API.

Let's start:

```php
// move phones to the proper place
$x100=Category::model()->findByPk(10);
$c200=Category::model()->findByPk(9);
$samsung=Category::model()->findByPk(7);
$x100->moveAsFirst($samsung);
$c200->moveBefore($x100);
// now move all Samsung phones branch
$mobile_phones=Category::model()->findByPk(1);
$samsung->moveAsFirst($mobile_phones);
// move the rest of phone models
$iphone=Category::model()->findByPk(6);
$iphone->moveAsFirst($mobile_phones);
$motorola=Category::model()->findByPk(8);
$motorola->moveAfter($samsung);
// move car models to appropriate place
$cars=Category::model()->findByPk(2);
$audi=Category::model()->findByPk(3);
$ford=Category::model()->findByPk(4);
$mercedes=Category::model()->findByPk(5);

foreach(array($audi,$ford,$mercedes) as $category)
    $category->moveAsLast($cars);
```

Result:

~~~
- 1. Mobile phones
	- 6. iPhone
	- 7. Samsung
		- 10. X100
		- 9. С200
	- 8. Motorola
- 2. Cars
	- 3. Audi
	- 4. Ford
	- 5. Mercedes
~~~

### Moving a node making it a new root

There is a special `moveAsRoot()` method that allows moving a node and making it
a new root. All descendants are moved as well in this case.

Example:

```php
$node=Category::model()->findByPk(10);
$node->moveAsRoot();
```

### Identifying node type

There are three methods to get node type: `isRoot()`, `isLeaf()`, `isDescendantOf()`.

Example:

```php
$root=Category::model()->findByPk(1);
CVarDumper::dump($root->isRoot()); //true;
CVarDumper::dump($root->isLeaf()); //false;
$node=Category::model()->findByPk(9);
CVarDumper::dump($node->isDescendantOf($root)); //true;
CVarDumper::dump($node->isRoot()); //false;
CVarDumper::dump($node->isLeaf()); //true;
$samsung=Category::model()->findByPk(7);
CVarDumper::dump($node->isDescendantOf($samsung)); //true;
```

Useful code
------------

### Non-recursive tree traversal

```php
$criteria=new CDbCriteria;
$criteria->order='t.lft'; // or 't.root, t.lft' for multiple trees
$categories=Category::model()->findAll($criteria);
$level=0;

foreach($categories as $n=>$category)
{
	if($category->level==$level)
		echo CHtml::closeTag('li')."\n";
	else if($category->level>$level)
		echo CHtml::openTag('ul')."\n";
	else
	{
		echo CHtml::closeTag('li')."\n";

		for($i=$level-$category->level;$i;$i--)
		{
			echo CHtml::closeTag('ul')."\n";
			echo CHtml::closeTag('li')."\n";
		}
	}

	echo CHtml::openTag('li');
	echo CHtml::encode($category->title);
	$level=$category->level;
}

for($i=$level;$i;$i--)
{
	echo CHtml::closeTag('li')."\n";
	echo CHtml::closeTag('ul')."\n";
}
```
