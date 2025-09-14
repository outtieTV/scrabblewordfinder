# Scrabble Word Finder by outtieTV
An Open-Source Self-Hosted Scrabble Word Finder<br />
1. Download Scrabble Word Finder source code and unzip it somewhere
2. Install PHP
3. cd to scrabblewordfinder directory that contains index.html
4. Download a wordlist.txt of your chosing to the above directory
5. Run fix-dictionaries.py with the wordlist.txt if needed
6. Edit scrabble_word_finder.php in a text editor and change the line that says $WORDLIST_FILE = __DIR__ . "/CSW24_modified.txt";
7. Run php -S 0.0.0.0:80
8. Open browser to localhost:80/scrabble_word_finder.php to generate scrabbleAnagrams.sqlite
9. Navigate to localhost:80 to use the word finder.
<br />
<br />
You can find word dictionaries at: https://boardgames.stackexchange.com/questions/38366/latest-collins-scrabble-words-list-in-text-file
<img width="1920" height="910" alt="image" src="https://github.com/user-attachments/assets/d72c71c9-d0ae-41b1-8235-10f8ef577551" />
<br />
<br />
HASBRO, its logo, and SCRABBLE® are trademarks of Hasbro in the U.S. and Canada. All content copyright its respective owners.
