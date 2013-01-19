DELETE ids, objects, trees 
FROM ids, objects, trees 
WHERE ids.id = objects.id AND ids.id = trees.object_id AND objects.is_virtual