Nested Set behavior 2 (preview version)
=======================================

This extension allows managing trees stored in database as nested sets.
It's implemented as Active Record behavior.

Installing and configuring
--------------------------

First you need to configure model as follows:

```php
class Category extends ActiveRecord
{
	public function behaviors() {
	    return array(
	        'tree' => array(
	            'class' => 'NestedSet',
	        ),
	    );
	}
}
```

Second you need to configure query model as follows:

```php
class CategoryQuery extends ActiveQuery
{
	public function behaviors() {
	    return array(
	        'tree' => array(
	            'class' => 'NestedSetQuery',
	        ),
	    );
	}
}
```

There is no need to validate fields specified in `leftAttribute`,
`rightAttribute`, `rootAttribute` and `levelAttribute` options. Moreover,
there could be problems if there are validation rules for these. Please
check if there are no rules for fields mentioned in model's rules() method.

In case of storing a single tree per database, DB structure can be built with
`schema/schema.sql`. If you're going to store multiple trees you'll need
`schema/schema_many_roots.sql`.

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

Using `NestedSet::roots()`:

```php
$roots = Category::find()->roots()->all();
```

Result:

Array of Active Record objects corresponding to Mobile phones and Cars nodes.

### Getting all descendants of a node

Using `NestedSet::descendants()`:

```php
$category = Category::find(1);
$descendants = $category->descendants()->all();
```

Result:

Array of Active Record objects corresponding to iPhone, Samsung, X100, C200 and Motorola.

### Getting all children of a node

Using `NestedSet::children()`:

```php
$category = Category::find(1);
$descendants = $category->children()->all();
```

Result:

Array of Active Record objects corresponding to iPhone, Samsung and Motorola.

### Getting all ancestors of a node

Using `NestedSet::ancestors()`:

```php
$category = Category::find(5);
$ancestors = $category->ancestors()->all();
```

Result:

Array of Active Record objects corresponding to Samsung and Mobile phones.

### Getting parent of a node

Using `NestedSet::parent()`:

```php
$category = Category::find(9);
$parent = $category->parent()->one();
```

Result:

Array of Active Record objects corresponding to Cars.

### Getting node siblings

Using `NestedSet::prev()` or
`NestedSet::next()`:

```php
$category = Category::find(9);
$nextSibling = $category->next()->one();
```

Result:

Array of Active Record objects corresponding to Mercedes.

### Getting the whole tree

You can get the whole tree using standard AR methods like the following.

For single tree per table:

```php
Category::find()->addOrderBy('lft')->all();
```

For multiple trees per table:

```php
Category::find()->where('root = ?', array($root_id))->addOrderBy('lft')->all();
```

Modifying a tree
----------------

In this section we'll build a tree like the one used in the previous section.

### Creating root nodes

You can create a root node using `NestedSet::saveNode()`.
In a single tree per table mode you can create only one root node. If you'll attempt
to create more there will be CException thrown.

```php
$root = new Category;
$root->title = 'Mobile Phones';
$root->saveNode();
$root = new Category;
$root->title = 'Cars';
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
$category1 = new Category;
$category1->title = 'Ford';
$category2 = new Category;
$category2->title = 'Mercedes';
$category3 = new Category;
$category3->title = 'Audi';
$root = Category::find(1);
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
$category1 = new Category;
$category1->title = 'Samsung';
$category2 = new Category;
$category2->title = 'Motorola';
$category3 = new Category;
$category3->title = 'iPhone';
$root = Category::find(2);
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
$category1 = new Category;
$category1->title = 'X100';
$category2 = new Category;
$category2->title = 'C200';
$node = Category::find(3);
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
$x100 = Category::find(10);
$c200 = Category::find(9);
$samsung = Category::find(7);
$x100->moveAsFirst($samsung);
$c200->moveBefore($x100);
// now move all Samsung phones branch
$mobile_phones = Category::find(1);
$samsung->moveAsFirst($mobile_phones);
// move the rest of phone models
$iphone = Category::find(6);
$iphone->moveAsFirst($mobile_phones);
$motorola = Category::find(8);
$motorola->moveAfter($samsung);
// move car models to appropriate place
$cars = Category::find(2);
$audi = Category::find(3);
$ford = Category::find(4);
$mercedes = Category::find(5);

foreach(array($audi, $ford, $mercedes) as $category) {
    $category->moveAsLast($cars);
}
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
$node = Category::find(10);
$node->moveAsRoot();
```

### Identifying node type

There are three methods to get node type: `isRoot()`, `isLeaf()`, `isDescendantOf()`.

Example:

```php
$root = Category::find(1);
VarDumper::dump($root->isRoot()); //true;
VarDumper::dump($root->isLeaf()); //false;
$node = Category::find(9);
VarDumper::dump($node->isDescendantOf($root)); //true;
VarDumper::dump($node->isRoot()); //false;
VarDumper::dump($node->isLeaf()); //true;
$samsung = Category::find(7);
VarDumper::dump($node->isDescendantOf($samsung)); //true;
```

Useful code
------------

### Non-recursive tree traversal

```php
$categories = Category::find()->addOrderBy('lft')->all();
$level = 0;

foreach ($categories as $n => $category)
{
	if ($category->level == $level) {
		echo Html::endTag('li') . "\n";
	} elseif ($category->level > $level) {
		echo Html::beginTag('ul') . "\n";
	} else {
		echo Html::endTag('li') . "\n";

		for ($i = $level - $category->level; $i; $i--) {
			echo Html::endTag('ul') . "\n";
			echo Html::endTag('li') . "\n";
		}
	}

	echo Html::beginTag('li');
	echo Html::encode($category->title);
	$level = $category->level;
}

for ($i = $level; $i; $i--) {
	echo Html::endTag('li') . "\n";
	echo Html::endTag('ul') . "\n";
}
```
