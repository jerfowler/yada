
		/**
		 * Model methods and operators implemented by the Mapper Class
         */
		 
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
		
		$test->count();    // Current count of returned records if loaded, or queries all records matching filters
		
		$test->reset();                          // Reset all unsaved changes and/or filters
		$test->reset('field');                   // Reset a single field's unsaved values and/or filters
		$test->reset('field1', 'field2');        // Reset a set of field's unsaved values and/or filters
		$test->reset(array('field1', 'field2')); // Functionally equivallent to above


		// Setting values 
		$test->field = $value;
		$test->field->set($value);
		
		// Getting values, *** through the model *** - this is not the same as iterating through the records... 
		// Note: Records have a different interface than models and values are retreived directly as properties or optionally as indexed array values
		// With models, the field property actually returns the mapper instance, this is how we can do operator chaining for each field
		
		$value = $test->field->get($default = NULL);  // Before the model is loaded, get will retreive any default values set for the field.
		                                              // After the model is loaded, get will retrieve the value from the current row.
													  // Pass a $default value to return if the field value is null
		
		// Replace
		$test->field->replace($search, $value);	// Works before or after loading records
		$test->field->repl_ex($regexp, $value); // Works only AFTER loading records
		

		// Filters and Operators

		// Operator Grouping
		$test->begin();
		$test->end();

		// Boolean Operators
		$test->and();
		$test->or();
		$test->and_not();
		$test->or_not();

		// Comparison
		$test->field->eq(1);     // equal
		$test->field->ns_eq(1);  // null-safe equal
		$test->field->not_eq(1); // not equal
		$test->field->gt(1);     // greater than
		$test->field->gt_eq(1);  // greater than or equal
		$test->field->lt(1);     // less than 
		$test->field->lt_eq(1);  // less than or equal

		// Conditional
		$test->field->is(NULL);
		$test->field->is(FALSE);
		$test->field->is(TRUE);
		$test->field->is_not(NULL);
		$test->field->is_not(FALSE);
		$test->field->is_not(TRUE);

		// Sets
		$test->field->in(1,2,3);          // use parameters
		$test->field->in(array(1,2,3));   // or pass an array, works for all set methods
		$test->field->not_in(1,2,3);
		$test->field->between(1,3);
		$test->field->not_between(1,3);

		// Relational
		$test->field->has($related);    // Has a relationship to ALL of the specified records
		$test->field->any($related);    // Has a relationship to ANY of the specified records
		$test->field->add($related);    // Add a relationship
		$test->field->remove($related); // Remove a relationship
		$test->field->remove();         // Remove all relationships
		$test->field->delete($related); // Deletes a related record 
		$test->field->delete();         // Deletes all related records

		// Allowable values passed to relational methods
		$related = 1;                                // integers == primary key
		$related = 'name';                           // strings == name key
		$related = $object;                          // objects instanceof Yada_Model
		$related = array(1,2,3);                     // array of integers == array of primary keys
		$related = array('name1', 'name2', 'name3'); // array of strings == array of name keys
		$related = array($object1, $object2);        // array of objects == array of Yada_Model
		$related = array('field1' => 'value1')       // array of key/value pairs == array of the related model's fields and their values


		$new_related = $test->related->new();       // Creates a new related record model
		$new_related = $test->related->new($values) // Creates a new related record with prefilled fields

