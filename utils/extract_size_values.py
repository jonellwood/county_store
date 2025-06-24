import os

sizeTable = [
        (0, 'XS'),
        (1, "S"), 
        (2, "Medium"), 
        (3, "Large"), 
        (4, "XL"),
        (5, "2XL"), 
        (6, "3XL"), 
        (7, "4XL"), 
        (8, "5XL"),
        (9, "6XL"), 
        (10, "7XL"), 
        (11, "8XL"), 
        (12, "9XL"), 
        (13, "10XL"),
        (14, "LT"),
        (15, "XLT"), 
        (16, "2XLT"), 
        (17, "3XLT"), 
        (18, "4XLT"),
        (30, "N/A"), 
]

file_path = './out'

def extract_size_range(size_str):
    print('Size String: ' + size_str)
    if not size_str:
        return []
    
    size_str = size_str.replace('–', '-')
    # sizes = [s.strip() for s in size_str.split("-")]
    sizes = size_str.strip().split("-")
    # print('Sizes after splitting:', sizes)
    # print(sizes)
    range_low = sizes[0]
    # print('????Range Low???')
    # print(range_low)
    range_hgh = sizes[1]
    # print('*****Range High*******')
    # print(range_hgh)
    size_map = {size[1]: size[0] for size in sizeTable}

    low_size_index = size_map.get(range_low)
    high_size_index = size_map.get(range_hgh)
    
    # Ensure low is <= to hgh (handle invalid ranges)
    if low_size_index is None or high_size_index is None or low_size_index > high_size_index:
        return []

    # Use the list to generte the list of sizes within the range
    return [sizeTable[i][0] for i in range(low_size_index, high_size_index + 1)]

   
    
# size_str = "Sizes: Adult Sizes: XS-4XL"
# size_str = "Sizes: Adult Sizes: XS-6XL"
# size_str = "Sizes: Adult Sizes: LT-3XLT"

# vals = extract_size_range(size_str.split(":")[-1])
# print(vals)

for file_name in os.listdir(file_path):
    # print(file_path)
    if file_name.endswith('.processed'):
        with open(os.path.join(file_path, file_name), 'r') as file:
            lines = file.readlines()
            sizes = []
            for line in lines:
                if line.startswith('Sizes:'):
                    parts = line.split(':')
                    # print('---------------')
                    # print(parts)
                    size_str = parts[2].strip()
                    # print('--------size str ---------')
                    # print(size_str)
                    with open(os.path.join(file_path, file_name), 'a') as outfile:
                        
                        size_vals = extract_size_range(size_str.split(":")[-1])
                        # print(size_vals)
                        outfile.write('\nSizeIndexValues:\n')
                        outfile.write(str(size_vals))
                        # for size in size_vals:
                        #     outfile.write(f"% {size['size']}]n")

        
            
print("Now run add-product-via-api.py")        
        
        # file_path = os.path.join(file_path, file_name)
        # process_file(file_path)