const fs = require('fs');
const markdownit = require('markdown-it'); // import markdownIt from 'markdown-it';

const markdownFilePath = './changelog.md';
const markdownText = fs.readFileSync(markdownFilePath, 'utf-8');

const md = markdownit();

const htmlText = md.render(markdownText);

const htmlFilePath = './changelog.html';
fs.writeFileSync(htmlFilePath, htmlText);

console.log(
	`Markdown file (${markdownFilePath}) successfully rendered to HTML (${htmlFilePath}).`
);
