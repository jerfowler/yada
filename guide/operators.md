

		$test->load();       // Load all records matching set filters/values
		$test->load(20);     // Load the first 20 records matching set filters/values
		$test->load(10, 20); // Load the first 10 records, offset of 20, matching set filters/values

		$test->save();       // Saves all changes, including updates/inserts/relational changes
		$test->save(TRUE);   // Forces an update even if the current DB record has changed since it was loaded
		$test->delete();     // deletes loaded records, or records matching filters

		$test->copy();   // Creates a new model with a copy of all vaules except its keys
		$test->clone();  // Creates a new instance of a model 

		$test->changed();  // Returns true if there are any unsaved values
		$test->loaded();   // Returns true if there are any active records
		$test->errors();   // Returns false if there are no errors, or an array of current errors
		
		$test->count();    // Current count of returned records if loaded, or all records matching filters
		
		$test->reset();        // Reset all unsaved changes and/or filters
		$test->reset('field'); // Reset a single field's unsaved values and/or filters
		$test->reset('field1', 'field2');        // Reset a set of field's unsaved values and/or filters
		$test->reset(array('field1', 'field2')); // Functionally equivallent to above


		// Setting values 
		$test->field = $value;
		$test->field->set($value);
		
		// Replace
		$test->field->replace($search, $value);
		$test->field->repl_ex($regexp, $value);
		
	
		// Filters and Operators

		// Operator Grouping
		$test->begin(); // 'and'
		$test->begin('and');
		$test->begin('or');
		$test->begin('and not');
		$test->begin('or not');
		$test->end();

		// Boolean
		$test->and();
		$test->or();
		$test->and('not');
		$test->or('not');

		// Comparison
		$test->id->eq(1);
		$test->id->ns_eq(1);
		$test->id->not_eq(1);
		$test->id->gt(1);
		$test->id->gt_eq(1);
		$test->id->lt(1);
		$test->id->lt_eq(1);

		// Conditional
		$test->id->is(NULL);
		$test->id->is(FALSE);
		$test->id->is(TRUE);
		$test->id->is_not(NULL);
		$test->id->is_not(FALSE);
		$test->id->is_not(TRUE);

		// Sets
		$test->id->in(1,2,3);
		$test->id->in(array(1,2,3));
		$test->id->not_in(1,2,3);
		$test->id->not_in(array(1,2,3));
		$test->id->between(1,3);
		$test->id->not_between(1,3);

		// Relational
		$test->related->has($related);    // Has a relationship to ALL of the specified records
		$test->related->any($related);    // Has a relationship to ANY of the specified records
		$test->related->add($related);    // Add a relationship
		$test->related->remove($related); // Remove a relationship
		$test->related->remove();         // Remove all relationships
		$test->related->delete($related); // Deletes a related record 
		$test->related->delete();         // Deletes all related records

		// Allowable values passed to relational methods
		$related = 1;                                // integers == primary key
		$related = 'name';                           // strings == name key
		$related = $object;                          // objects instanceof Yada_Model
		$related = array(1,2,3);                     // array of integers == array of primary keys
		$related = array('name1', 'name2', 'name3'); // array of strings == array of name keys
		$related = array($object1, $object2);        // array of objects == array of Yada_Model
		$related = array('field1' => 'value1')       // array of key/value pairs == array of the related model's fields and their values


		$new_related = $test->related->new();       // Creates a new related record 
		$new_related = $test->related->new($values) // Creates a new related record with prefilled fields

