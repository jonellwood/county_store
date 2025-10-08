import os
import json

def extract_product_data():
    """
    Reads all JSON files in current directory and extracts
    normalized_code and final_url from each file
    """
    current_dir = os.getcwd()
    extracted_data = []
    
    # Get all JSON files in current directory
    json_files = [f for f in os.listdir(current_dir) if f.endswith('.json')]
    
    print(f"Found {len(json_files)} JSON files to process...")
    
    for filename in json_files:
        filepath = os.path.join(current_dir, filename)
        
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                data = json.load(f)
                
                # Extract normalized_code and final_url
                normalized_code = data.get('normalized_code', 'N/A')
                final_url = data.get('result', {}).get('data', {}).get('final_url', 'N/A')
                
                extracted_data.append({
                    'file': filename,
                    'code': normalized_code,
                    'url': final_url
                })
                
                print(f"✓ Processed: {filename}")
                    
        except Exception as e:
            print(f"✗ Error reading {filename}: {e}")
    
    # Write results to output file
    output_file = 'product_data_output.txt'
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("Product Codes and URLs\n")
        f.write("=" * 80 + "\n\n")
        
        for item in extracted_data:
            f.write(f"Code: {item['code']}\n")
            f.write(f"URL:  {item['url']}\n")
            f.write(f"File: {item['file']}\n")
            f.write("-" * 80 + "\n\n")
        
        f.write(f"\nTotal: {len(extracted_data)} products processed\n")
    
    print(f"\n{'='*50}")
    print(f"Processed {len(extracted_data)} file(s)")
    print(f"Results written to: {output_file}")

if __name__ == "__main__":
    extract_product_data()