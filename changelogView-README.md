# Changelog Usage

1. Create / Update changelog.md file and save.
2. Run <code>node changelogView.js </code>
3. Push <code> changelog.html </code> AND <code> changelog.md </code> to production. PHP uses the first found H2 Element found in the .md file to set the App Version Number which is primary displayed in the footer of the application. (FYI: If you deviate from the format in the .md file, you will need to update the function that set that variable value as well. )

changelogView.js uses <code>markdown-it</code> to parse your markdown with changelog.html as the output source.

changelogView.php contains other data points needed and the body of the page is an echo if the changelog.html file.

&#128580; YES there is probably a better way to do this ... but this works, its easy, and it renders super fast because it is only html and css.
