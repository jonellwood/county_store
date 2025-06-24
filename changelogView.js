// const fs = require('fs');
import fs from 'fs';
// const markdownit = require('markdown-it'); // import markdownIt from 'markdown-it';

import MarkdownIt from 'markdown-it';

const md = MarkdownIt();

const markdownFilePath = './changelog.md';
const markdownText = fs.readFileSync(markdownFilePath, 'utf-8');

const htmlText = md.render(markdownText);

const htmlFilePath = './changelog.html';
fs.writeFileSync(htmlFilePath, htmlText);

console.log(
	`Markdown file (${markdownFilePath}) successfully rendered to HTML (${htmlFilePath}).`
);
