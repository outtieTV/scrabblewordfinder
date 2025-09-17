# Scrabble Word Finder by outtieTV
An Open-Source Self-Hosted Scrabble Word Finder<br />
<br />
On Windows/Mac/Linux:
1. Download Scrabble Word Finder source code and unzip it somewhere
2. Install PHP
3. cd to scrabblewordfinder directory that contains index.html
4. Download a wordlist.txt of your choosing to the above directory
5. Run fix-dictionaries.py with the wordlist.txt if needed
6. Choose either scrabble_word_finder.php for sqlite or scrabble_word_finder_sql.php for mysql 8+
7. Rename scrabble_word_finder_sql.php to scrabble_word_finder.php if using sql mode.
8. Edit scrabble_word_finder.php in a text editor and change the line that says $WORDLIST_FILE = __DIR__ . "/CSW24_modified.txt";
9. Run php -S 0.0.0.0:80
10. Open browser to localhost:80/scrabble_word_finder.php to generate scrabbleAnagrams.sqlite or scrabbleDB
11. Navigate to localhost:80 to use the word finder.
<br />
<br />
ScrabbleWordFinder on Termux on Android:<br />
1. Download F-Droid
2. Download Termux through F-Droid
3. Open Termux
4. $ termux-setup-storage
5. $ termux-change-repo
6. $ pkg update && pkg upgrade
7. $ pkg install php sqlite
8. $ pkg install wget
9. $ cd ~
12. $ wget https://github.com/outtieTV/scrabblewordfinder/archive/refs/heads/main.zip
13. $ unzip main.zip
14. $ chmod -R a+wx scrabblewordfinder-main
15. $ cd scrabblewordfinder
16. $ ifconfig -a
17. $ termux-wake-lock
18. $ php -S 0.0.0.0:8000
<br /><br />
You can find word dictionaries at: https://boardgames.stackexchange.com/questions/38366/latest-collins-scrabble-words-list-in-text-file
<img width="1920" height="910" alt="image" src="https://github.com/user-attachments/assets/d72c71c9-d0ae-41b1-8235-10f8ef577551" />
<br />
<br />
Edit to mention: if you want to easily keep track of Scrabble scores, use Microsoft Excel or Google Sheets and type =SUM(A:A) to sum a whole column of scores or =SUM(A2:A) to ignore headers for names.<br /><br />
<br />
HASBRO, its logo, and SCRABBLE® are trademarks of Hasbro in the U.S. and Canada. All content copyright its respective owners.
