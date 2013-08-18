DELETE ids, objects, parents, protos
FROM ids, objects, parents, protos
WHERE ids.id = objects.id AND ids.id = parents.object_id AND ids.id = protos.object_id AND ids.id IN ()