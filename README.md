# Akeeba DocImport³

The simplest way to integrate DocBook XML documentation in your Joomla! site.

## Internal project - No support

Akeeba DociMport is a project internal to Akeeba Ltd. We use it as own site's documentation system. We make it available free of charge to everyone in hope that it will be useful. However, we will not accept any feature requests, feature patches or support requests. Emails (including through our business site's or personal sites' contact forms), GitHub Issues and Pull Requests containing any of these will be deleted / closed without reply. Thank you for your understanding.

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
* **translations**	[Akeeba Translations](https://github.com/akeeba/translations)
* **fof3**			[Framework on Framework 3.x](https://github.com/akeeba/fof)

You will need to use the exact folder names specified here.

### Initialising the repository

All of the following commands are to be run from the MAIN directory.

1. You will first need to do the initial link with Akeeba Build Tools, running the following command

		php ../buildfiles/tools/link.php `pwd`
		
	or, on Windows:
	
		php ../buildfiles/tools/link.php %CD%
		
2. After the initial linking takes place, go inside the build directory:

		cd build
		
	and run the Phing task called link:
	
		phing link
		
	If you are on Windows make sure that you are running an elevated command prompt (run cmd.exe as Administrator)
	
### Useful Phing tasks

All of the following commands are to be run build directory inside the MAIN directory.

#### Symlinking to a Joomla! installation
This will create symlinks and hardlinks from your working directory to a locally installed Joomla! site. Any changes you perform to the repository files will be instantly reflected to the site, without the need to deploy your changes.

	phing relink -Dsite=/path/to/site/root
	
or, on Windows:

	phing relink -Dsite=c:\path\to\site\root
	
**Examples**

	phing relink -Dsite=/var/www/html/joomla
	
or, on Windows:
	
	phing relink -Dsite=c:\path\to\site\root\joomla

#### Relinking internal files

This is required after every major upgrade in the component and/or when new plugins and modules are installed. It will create symlinks from the various external repositories to the MAIN directory.

	phing link
	
#### Creating a dev release installation package

This creates the installable ZIP packages of the component inside the MAIN/release directory.

	phing git

Please note that it's necessary to do a package build for FOF and Strapper with `git pull` and `phing git` commands in your copy of the FOF and Strapper repositories before building an Akeeba Subscriptions package. For more details see the package build instructions on the [FOF page](https://github.com/akeeba/fof) and [Strapper page](https://github.com/akeeba/strapper). Failure to do so will either result in a failure to create a package, an uninstallable package or will end up overwriting the already installed FOF and/or Strapper on your site with an older version, resulting in potentially severe issues in other FOF-based and Strapper-based components.
	
#### Build the documentation in PDF format

This builds the documentation in PDF format using the DocBook XML sources found in the documentation directory.

	phing documentation
