<?php
// Script to sync knowledge files to the public directory

// Copy website_knowledge.txt to public/
copy('website_knowledge.txt', 'public/website_knowledge.txt');
echo "Copied website_knowledge.txt to public/\n";

// Copy knowledge.txt to public/
copy('knowledge.txt', 'public/knowledge.txt');
echo "Copied knowledge.txt to public/\n";

if (file_exists('app_knowledge.txt')) {
    copy('app_knowledge.txt', 'public/app_knowledge.txt');
    echo "Copied app_knowledge.txt to public/\n";
}

echo "Knowledge files synced successfully\n";
