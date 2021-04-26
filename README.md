# Akeeba DocImport³

The simplest way to integrate DocBook XML documentation in your Joomla! site.

## Internal project - No support

Akeeba DocImport is a project internal to Akeeba Ltd. We use it as own site's documentation system. We make it available free of charge to everyone in hope that it will be useful. However, we will not accept any feature requests, feature patches or support requests. Emails (including through our business site's or personal sites' contact forms), GitHub Issues and Pull Requests containing any of these will be deleted / closed without reply. Thank you for your understanding.

## Downloads

We provide _infrequent_ builds available for download from [this repository's Releases section](https://github.com/akeeba/docimport/releases). Please note that these are not released or maintained regularly. We urge developers to build their own packages using the instructions provided below.

## Prerequisites

In order to build the installation packages of this component you will need to have the following tools:

* A command line environment. Using Bash under Linux / Mac OS X works best. On Windows you will need to run most tools through an elevated privileges (administrator) command prompt on an NTFS filesystem due to the use of symlinks. Press WIN-X and click on "Command Prompt (Admin)" to launch an elevated command prompt.
* A PHP CLI binary in your path
* Command line Git executables
* PEAR and Phing installed, with the Net_FTP and VersionControl_SVN PEAR packages installed
* (Optional) libxml and libsxlt command-line tools, only if you intend on building the documentation PDF files

You will also need the following path structure inside a folder on your system

* **docimport**		This repository
* **buildfiles**	[Akeeba Build Tools](https://github.com/akeeba/buildfiles)

You will need to use the exact folder names specified here.

### Initialising the repository

2. Go inside the build directory:

		cd build
		
	and run the Phing task called git:
	
		phing git
		
	If you are on Windows make sure that you are running an elevated command prompt (run cmd.exe as Administrator)
	
### Useful Phing tasks

All of the following commands are to be run build directory inside the `build` directory.

#### Symlinking to a Joomla! installation
This will create symlinks and hardlinks from your working directory to a locally installed Joomla! site. Any changes you perform to the repository files will be instantly reflected to the site, without the need to deploy your changes.

	phing relink -Dsite=/path/to/site/root
	
or, on Windows:

	phing relink -Dsite=c:\path\to\site\root
	
**Examples**

	phing relink -Dsite=/var/www/html/joomla
	
or, on Windows:
	
	phing relink -Dsite=c:\path\to\site\root\joomla

#### Creating a dev release installation package

This creates the installable ZIP packages of the component inside the `release` directory.

	phing git
