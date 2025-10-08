import os
import json

def find_tbd_in_json_files():
    """
    Searches all JSON files in the current directory for the string 'TBD'
    and writes matching filenames to output.txt
    """
    current_dir = os.getcwd()
    files_with_tbd = []
    
    # Get all JSON files in current directory
    json_files = [f for f in os.listdir(current_dir) if f.endswith('.json')]
    
    print(f"Found {len(json_files)} JSON files to check...")
    
    for filename in json_files:
        filepath = os.path.join(current_dir, filename)
        
        try:
            # Read the file as text to search for 'TBD'
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                
                if 'TBD' in content:
                    files_with_tbd.append(filename)
                    print(f"✓ Found TBD in: {filename}")
                    
        except Exception as e:
            print(f"✗ Error reading {filename}: {e}")
    
    # Write results to output file
    output_file = 'output.txt'
    with open(output_file, 'w', encoding='utf-8') as f:
        if files_with_tbd:
            f.write("Files containing 'TBD':\n")
            f.write("=" * 50 + "\n")
            for filename in files_with_tbd:
                f.write(f"{filename}\n")
        else:
            f.write("No files containing 'TBD' were found.\n")
    
    print(f"\n{'='*50}")
    print(f"Found {len(files_with_tbd)} file(s) containing 'TBD'")
    print(f"Results written to: {output_file}")

if __name__ == "__main__":
    find_tbd_in_json_files()