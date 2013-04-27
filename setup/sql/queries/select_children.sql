SELECT * FROM ids, objects, parents
WHERE ids.id = objects.id AND ids.id = parents.object_id AND parents.parent_id = 2