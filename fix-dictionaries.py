import os

def process_wordlist(input_filepath):
    """
    Reads a text file, removes everything after the first space on each line,
    and saves the modified content to a new file.
    """
    # Check if the file exists
    if not os.path.exists(input_filepath):
        print(f"Error: The file '{input_filepath}' does not exist.")
        return

    # Create the output filename
    # This adds '_modified' before the file extension
    directory, filename = os.path.split(input_filepath)
    name, extension = os.path.splitext(filename)
    output_filepath = os.path.join(directory, f"{name}_modified{extension}")

    try:
        with open(input_filepath, 'r', encoding='utf-8') as infile, \
             open(output_filepath, 'w', encoding='utf-8') as outfile:
            for line in infile:
                # Split the line by the first space and take the first part
                first_word = line.split(' ', 1)[0]
                outfile.write(first_word + '\n')
        
        print(f"Success! The modified wordlist has been saved to '{output_filepath}'.")

    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    file_location = input("Please enter the full path to your text file (e.g., C:\\Users\\YourName\\wordlist.txt): ")
    process_wordlist(file_location)
