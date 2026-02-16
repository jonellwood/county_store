import os

TARGET = "sm-c"

folder = os.getcwd()

for name in os.listdir(folder):
    old_path = os.path.join(folder, name)

    # skip directories
    if not os.path.isfile(old_path):
        continue

    if TARGET in name:
        new_name = name.replace(TARGET, "")
        new_path = os.path.join(folder, new_name)

        if os.path.exists(new_path):
            print(f"SKIP (exists): {new_name}")
            continue

        os.rename(old_path, new_path)
        print(f"RENAMED: {name} -> {new_name}")

print("Done. Files cleansed of sm-c.")
