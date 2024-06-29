import re
import json

def find_css_classes_and_selectors(html_file_path):
    # Regular expression patterns
    css_class_pattern = r'class="([^"]+)"'
    attribute_selector_pattern = r'\.(?!then|catch|try)(.*?)\s*\{'
    
    # Read the content of the HTML file
    with open(html_file_path, 'r', encoding='utf-8') as file:
        content = file.read()

    # Find all matches for CSS classes
    css_classes_matches = re.findall(css_class_pattern, content)

    # Find all matches for attribute selectors
    attribute_selectors_matches = re.findall(attribute_selector_pattern, content)

    # Find attribute selectors that do not have a corresponding match in CSS classes
    unmatched_attribute_selectors = set(attribute_selectors_matches) - set(css_classes_matches)

    # Combine results
    results = {
        'CSS Classes': css_classes_matches,
        'Unmatched Attribute Selectors': list(unmatched_attribute_selectors)
    }

    return results

# Example usage
html_file_path = '../viewCart.php'  # Replace with your HTML file path
results = find_css_classes_and_selectors(html_file_path)

# Write results to a text file
output_file_path = 'output_results.txt'
with open(output_file_path, 'w', encoding='utf-8') as outfile:
    json.dump(results, outfile, indent=4)


print('Results printed to file' )