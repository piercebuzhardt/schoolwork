import os, subprocess, re

# Set this string == your site home directory
# Then add this python script's filename/path to your .gitignore in said directory
site_home="/home/jdhartl/public_html/cpsc4910"

# Defined conventions for 'proper' file permissions
dir_permissions = oct(0o711)
ext_permissions = {}
ext_permissions['sh'] = oct(0o700)
ext_permissions['py'] = oct(0o700)
ext_permissions['php'] = oct(0o644)
ext_permissions['html'] = oct(0o644)
ext_permissions['htm'] = oct(0o644)
ext_permissions['css'] = oct(0o644)
other_permissions = oct(0o644)

# Recursive function to fix directory contents of self, then children
def crawl_dir(fullname):
  # Move to directory
  os.chdir(fullname)
  #print('Visiting '+fullname)

  # Make sure directory permissions are correct
  permission = oct(os.stat(fullname).st_mode & 0o777)
  if permission != dir_permissions:
    os.chmod(fullname, int(dir_permissions, 8))
    print('Updated directory '+fullname+' permissions from '+permission+' to '+dir_permissions)

  # Find directories within this directory (exclude those that start with '.')
  other_dirs = [d for d in os.listdir('.') if os.path.isdir(d) and d[0] != '.']

  # Get full name for new paths
  cwd = (subprocess.check_output(["pwd"])).decode('ascii').rstrip('\n')
  to_crawl = [cwd+'/'+d for d in other_dirs]

  # Find all non-directory files in current directory (exclude those that start with '.')
  files = [f for f in os.listdir('.') if os.path.isfile(f) and f[0] != '.']
  for f in files:
    #print('\tLocated file: '+fullname+'/'+f)
    # Negative lookahead searches for LAST '.' in name, then capture everything after it
    extension = re.search(r"(\.)(?!.*\.)(.*)", f).group(2)
    #print('\t\tExtension: '+extension)
    permission = oct(os.stat(fullname+'/'+f).st_mode & 0o777)
    #print('\t\tPermission: '+permission)
    # Check permission matching, fix when not matching
    if extension in ext_permissions.keys():
      if permission != ext_permissions[extension]:
        #print('\t\t\tPermission does not align, should be: '+ext_permissions[extension])
        os.chmod(fullname+'/'+f, int(ext_permissions[extension], 8))
        print('Updated '+fullname+'/'+f+' permissions from '+permission+' to '+ext_permissions[extension])
    else:
      if permission != other_permissions:
        #print('\t\t\tPermission does not align, should be: '+other_permission)
        os.chmod(fullname+'/'+f, int(other_permissions, 8))
        print('Updated '+fullname+'/'+f+' permissions from '+permission+' to '+other_permissions)

  # Recursively crawl new paths
  #for d in to_crawl:
  #  crawl_dir(d)

crawl_dir(site_home)

