import os

def remove_product_names(directory, color_directory):
    # Init an empty array to store the extracted prodcuct names
    extracted_strings = []

    for filename in os.listdir(directory):
        # if filename.endswith('.processed') and filename.startswith('SM-'):
        if filename.startswith('SM-'):
            # Extract the string between 'SM-' and '.txt'
            # extracted_string = filename[3:-4]
            extracted_string = filename[3:filename.index('.txt')] #I THINK this is what I want.....
            extracted_strings.append(extracted_string.lower())
    print('Removing the following', extracted_strings)
  
    # for filename in os.listdir(color_directory):
    #     for extracted_string in extracted_strings:
    #         if extracted_string in filename:
    #             new_filename = filename.replace(extracted_string, '')

    #             if new_filename != filename:
    #                 os.rename(os.path.join(color_directory, filename), os.path.join(color_directory, new_filename))
    #                 print(f"Remove product numbers renamed: {filename} to {new_filename}")
    ######################################################
    for filename in os.listdir(color_directory):
        for extracted_string in extracted_strings:
            if extracted_string in filename:
                # Check if extracted string is preceded by a hyphen
                if filename.startswith('-' + extracted_string):
                    new_filename = filename.replace('-' + extracted_string, '', 1)
                # Check if extracted string is followed by a hyphen
                elif filename.endswith(extracted_string + '-'):
                    new_filename = filename.replace(extracted_string + '-', '', 1)
                else:
                    new_filename = filename.replace(extracted_string, '')

                if new_filename != filename:
                    os.rename(os.path.join(color_directory, filename), os.path.join(color_directory, new_filename))
                    print(f"Remove product numbers renamed: {filename} to {new_filename}")

# directory = './out'
# color_directory = './images_down'
# remove_product_names(directory, color_directory)
