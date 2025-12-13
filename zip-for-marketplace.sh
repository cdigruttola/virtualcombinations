rm -rf tmp
mkdir -p tmp/is_productextratabs
cp -R classes tmp/is_productextratabs
cp -R controllers tmp/is_productextratabs
cp -R config tmp/is_productextratabs
cp -R docs tmp/is_productextratabs
cp -R override tmp/is_productextratabs
cp -R sql tmp/is_productextratabs
cp -R src tmp/is_productextratabs
cp -R translations tmp/is_productextratabs
cp -R views tmp/is_productextratabs
cp -R upgrade tmp/is_productextratabs
cp -R vendor tmp/is_productextratabs
cp -R index.php tmp/is_productextratabs
cp -R logo.png tmp/is_productextratabs
cp -R is_productextratabs.php tmp/is_productextratabs
cp -R config.xml tmp/is_productextratabs
cp -R LICENSE tmp/is_productextratabs
cp -R README.md tmp/is_productextratabs
cd tmp && find . -name ".DS_Store" -delete
zip -r is_productextratabs.zip . -x ".*" -x "__MACOSX"
