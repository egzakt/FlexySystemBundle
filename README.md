EgzaktSystemBundle
==================

EgzaktSystemBundle

### ImageTypeExtension
To add an image preview to a file field, specify `image_path` option for the file field, in buildForm() method.
To use a custon Imagine filter, use `image_filter` option. Exemple for contact bundle:
`$builder->add('iconFile', 'file', array('image_path' => 'iconPath'));`
