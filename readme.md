# Yada: Yet Another Data Abstraction

For the [Kohana Framework](http://kohanaframework.org/).

NOTICE: THIS IS PRE-ALPHA CODE... nothing much works yet...

Goals:

1. Simple and intuitive to use
2. Data source agnostic (SQL & NoSQL) 
3. Modular and Highly Extensible
4. Record level and Collection level modifications
5. More... As I think of them...

Example:

1. Get the first 20 user objects with a name that starts with 'Bob'.

		$users = Yada::factory('User');
		$users->name->like('Bob%');
		$users->load(20);

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
		// Or as a set;
		$user->save();
		
4. Create new records
		$product = Yada::factory('User');
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