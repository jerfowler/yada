# Yada: To know in a relational sense.

For the [Kohana Framework](http://kohanaframework.org/).

NOTICE: THIS IS PRE-ALPHA CODE... nothing much works yet...

This is an entirely new Object-Relational Mapping module that uses both Active Record and Data Mapper Patterns. 

This library is mostly new code, but some ideas, functions, and possibly source code may have been borrowed 
from similar libraries ([ORM](http://github.com/kohana/orm), [Sprig](http://github.com/shadowhand/sprig), [Jelly](http://github.com/jonathangeiger/kohana-jelly))

Credit and thanks goes out to those authors.

Goals:

1. Simple and intuitive to use, joins are automatic, SQL is hidden
2. Object Aggregation for a Modular Model Framework that is Highly Extensible
3. Data source agnostic (SQL, NoSQL, FQL, YQL, XML, JSON) 
4. Record level and Collection level modifications
5. Minimal and efficient data retrieval and caching
6. Composite/Polymophic Keys
7. Calculated Fields
8. Standard One-to-One, Many-to-One, Many-to-Many Relationships
9. Hierarchical Relationships (Adjacency List, MPTT, Transitive Closure)
10. Customizable Inputs & Forms
11. Scaffolding system
12. More... As I think of them...

Examples:

1. Get the first 20 user objects with a name that starts with 'Bob'.

		$users = Yada::factory('User');
		$users->name->like('Bob%');
		$users->load(20);
		
2. Get the next 10 user objects

		$users->load(10,20);
		
2. Change their name to Robert.

		$users->name->replace('Bob', 'Robert');
		$users->save();
	
3. Or iterate through them one at a time.

		foreach($users as $user)
		{
			$user->name->replace('Bob', 'Robert');
			// Update them individually
			$user->save();
		}
		// Or make changes then update as a set;
		$users->save();

4. Modify sets of records without iterating

		$products = Yada::factory('Product');
		$products->name->replace('exmple', 'example')
		$products->name->like('%exmple%')
		$products->save();

5. Create new records

		$product = Yada::factory('Product');
		// With normal assignment statements
		$product->name = 'Gizmo';
		$product->price = 20;
		
		// ...or by passing an associative array
		$product->values(array('name'=>'Gizmo', 'price' => 20));
		
		// ...or with method chaining
		$product->name->set('Gizmo')->price->set(20);
		
		// Save the record.
		$product->save();
		


More examples to come....