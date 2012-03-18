\documentclass[ngerman,final,fontsize=12pt,paper=a4,twoside,bibliography=totocnumbered,BCOR=8mm,draft=false]{scrartcl}

\usepackage[LGRx,T1]{fontenc}
\usepackage[ngerman]{babel}
\usepackage[utf8]{inputenx}
\usepackage[sort&compress,square]{natbib}
\usepackage[babel]{csquotes}
\usepackage[hyphens]{url}
\usepackage[draft=false,final,plainpages=false,pdftex]{hyperref}
\usepackage{eso-pic}
\usepackage{graphicx}
\usepackage{xcolor}
\usepackage{pdflscape}
\usepackage{longtable}
\usepackage{framed}
\usepackage{textcomp}
\usepackage{textgreek}

\usepackage[charter,sfscaled]{mathdesign}

%\usepackage[spacing=true,tracking=true,kerning=true,babel]{microtype}
\usepackage[spacing=true,kerning=true,babel]{microtype}

%\setparsizes{1em}{.5\baselineskip}{0pt plus 1fil}

\author{VroniPlag} 

<?php
require 'config.php';
require 'loadParameters.php';
?>
\title{<?php print $TITEL1;?>}
\subtitle{<?php print $TITEL2;?>}
\publishers{\url{<?php print 'http://de.vroniplag.wikia.com/wiki/'.NAME_PREFIX.'/'.BERICHT_SEITE;?>}}

\hypersetup{%
        pdfauthor={VroniPlag},%
	pdftitle={<?php print $TITEL1.' -- '.$TITEL2;?>},%
        pdflang={en},%
        pdfduplex={DuplexFlipLongEdge},%
        pdfprintscaling={None},%
	linktoc=all,%
<?php
if($abLinks === 'color' || $abLinks === 'color+underline' || $abLinks === 'color+box') {
	print "\t".'colorlinks,%'."\n";
} else if($abLinks === 'underline') {
	print "\t".'colorlinks=false,%'."\n";
	print "\t".'pdfborderstyle={/S/U/W 1},%'."\n";
	print "\t".'pdfborder=0 0 1,%'."\n";
} else if($abLinks === 'box') {
	// nothing to do
} else if($abLinks === 'none') {
	print "\t".'draft,%'."\n";
}
if($abEnableLinkColors === 'yes') {
	print "\t".'linkcolor='.$abInternalLinkColor.',%'."\n";
	print "\t".'citecolor='.$abSourceLinkColor.',%'."\n";
	print "\t".'filecolor='.$abExternalLinkColor.',%'."\n";
	print "\t".'urlcolor='.$abExternalLinkColor.',%'."\n";
	print "\t".'linkbordercolor={'.$abInternalLinkBorderColor.'},%'."\n";
	print "\t".'citebordercolor={'.$abSourceLinkBorderColor.'},%'."\n";
	print "\t".'filebordercolor={'.$abExternalLinkBorderColor.'},%'."\n";
	print "\t".'urlbordercolor={'.$abExternalLinkBorderColor.'},%'."\n";
} else {
	print "\t".'linkcolor=black,'."\n";
	print "\t".'citecolor=black,'."\n";
	print "\t".'filecolor=black,'."\n";
	print "\t".'urlcolor=black,'."\n";
	print "\t".'linkbordercolor={0 0 0},'."\n";
	print "\t".'citebordercolor={0 0 0},'."\n";
	print "\t".'filebordercolor={0 0 0},'."\n";
	print "\t".'urlbordercolor={0 0 0},'."\n";
}

?>
}

\definecolor{shadecolor}{rgb}{0.95,0.95,0.95} 

\newenvironment{fragment}
	{\begin{snugshade}}
	{\end{snugshade}
	 \penalty-200
	 \vskip 0pt plus 10mm minus 5mm}
\newenvironment{fragmentpart}[1]
	{\noindent\textbf{#1}\par\penalty500}
	{\par}
\newcommand{\BackgroundPic}
	{\put(0,0){\parbox[b][\paperheight]{\paperwidth}{%
		\vfill%
		\centering%
		\includegraphics[width=\paperwidth,height=\paperheight,%
			keepaspectratio]{background.png}%
		\vfill%
	}}}

%\setkomafont{chapter}{\Large}
\setkomafont{section}{\large}
\addtokomafont{disposition}{\normalfont\boldmath\bfseries}
\urlstyle{rm}

\begin{document}

<?php
# color+underline und color+box muessen nach \begin{document} behandelt werden
if($abLinks === 'color+underline') {
	print "\hypersetup{%\n";
	print "\t".'pdfborderstyle={/S/U/W 1},%'."\n";
	print "\t".'pdfborder=0 0 1,%'."\n";
	print "}\n";
} else if($abLinks === 'color+box') {
	print "\hypersetup{%\n";
	print "\t".'pdfborderstyle={/S/S/W 1},%'."\n";
	print "\t".'pdfborder=0 0 1,%'."\n";
	print "}\n";
}
?>

%\AddToShipoutPicture*{\BackgroundPic}
\maketitle\thispagestyle{empty}
%\ClearShipoutPicture

\tableofcontents
\newpage

<?php require_once('importWiki.php'); ?>

\appendix
\section{Textnachweise}

<?php require_once('importFragmente.php'); ?>
\bibliographystyle{dinat-custom}
\renewcommand{\refname}{Quellenverzeichnis}
\bibliography{ab}
\end{document}
