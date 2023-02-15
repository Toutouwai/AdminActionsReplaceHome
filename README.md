# Replace Home

An action for the [Admin Actions](https://modules.processwire.com/modules/process-admin-actions/) module for ProcessWire CMS/CMF. The action replaces the template and content of the home page with that of a selected page.

Sometimes there is a need to develop a new home page while keeping the existing home page in place until the new version is ready and approved. Then you want to apply the new home page, but this is not so easy to do in ProcessWire since home is a special page that cannot simply be replaced by another page.

## What the action does

* Removes all the fields from the home template, which simultaneously deletes all the content from the home page.
* Adds all the fields from the selected source page template and applies any template-specific overrides.
* Sets the home page field values to match the source page.
* Updates all file and image URLs in any textareas so they point to the home page files rather than the original source page.

## What the action doesn't do

* The action does not modify the home template file. You will probably want to update the home template file immediately after running the action (e.g. copy/paste the code of the source page template file).
* The action does not automatically delete the source page – that can be done manually after you have confirmed that the action was successfull.

## Warning

**This action is destructive!** It deletes content/files/images from the existing home page. In addition to the automatic Admin Actions database backup you should create a backup of **/site/assets/files/** before running this action, and consider also making a manual database backup for extra safety.

## Usage

1. [Install](http://modules.processwire.com/install-uninstall/) the Replace Home module.
2. Visit the Admin Actions config screen and enable the "Replace Home" action for the roles who are allowed to use it.
3. Create file system and database backups – see the warning section above.
4. Navigate to Admin Actions > Replace Home, select the source page whose template and content will replace the home page, then execute the action.
5. Update the home template file if needed.

## Screenshot

![replace-home](https://user-images.githubusercontent.com/1538852/218973101-014230a5-c06e-42fc-84d1-f6d595b2e0fe.png)