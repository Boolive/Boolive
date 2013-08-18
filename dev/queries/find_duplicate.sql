SELECT ids.* FROM ids
JOIN ids id2 ON (ids.uri = id2.uri && ids.id != id2.id)
JOIN objects o2 ON (o2.id = ids.id)