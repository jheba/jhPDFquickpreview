<?php

/*!
  \class   TemplatePdfquickpreviewOperator templatepdfquickpreviewoperator.php
  \ingroup eZTemplateOperators
  \brief   Handles template operator pdfquickpreview. By using pdfquickpreview you can convert pdf document to a image slide show.
  \version 1.0
  \date    Saturday 19 October 2013 11:07:06 pm
  \author  Jarosław Heba

  

  Example:
\code
{$node|pdfquickpreview( 'large', '1..5,7,9,12' )}
\endcode
*/

/*
If you want to have autoloading of this operator you should create
a eztemplateautoload.php file and add the following code to it.
The autoload file must be placed somewhere specified in AutoloadPath
under the group TemplateSettings in settings/site.ini

$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'templatepdfquickpreviewoperator.php',
                                    'class' => 'TemplatePdfquickpreviewOperator',
                                    'operator_names' => array( 'pdfquickpreview' ) );

If your template operator is in an extension, you need to add the following settings:

To extension/YOUREXTENSION/settings/site.ini.append:
---
[TemplateSettings]
ExtensionAutoloadPath[]=YOUREXTENSION
---

To extension/YOUREXTENSION/autoloads/eztemplateautoload.php:
----
$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'extension/YOUEXTENSION/YOURPATH/templatepdfquickpreviewoperator.php',
                                    'class' => 'TemplatePdfquickpreviewOperator',
                                    'operator_names' => array( 'pdfquickpreview' ) );
---

Create the files if they don't exist, and replace YOUREXTENSION and YOURPATH with the correct values.

*/


class TemplatePdfquickpreviewOperator
{
    /*!
      Constructor, does nothing by default.
    */
    function TemplatePdfquickpreviewOperator()
    {
    }

    /*!
     \return an array with the template operator name.
    */
    function operatorList()
    {
        return array( 'pdfquickpreview' );
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array( 'pdfquickpreview' => array( 'variant' => array( 'type' => 'string',
                                                                          'required' => false,
                                                                          'default' => 'default text' ),
                                                  'pages' => array( 'type' => 'string',
                                                                           'required' => false,
                                                                           'default' => '' 
                                                                           /*
                                                                                This value is a coma-separated list of digits. Allowed combinations:
                                                                                1,3,5 - displays pages 1, 3 and 5
                                                                                1..5 - displays pages from 1, 2, 3, 4 and 5
                                                                                1,3..5,7 - displays pages 1, 3, 4, 5 and 7
                                                                            */
                                                                       ) ) 
        );
    }


    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
        list( $variant, $width, $height, $pages, $fileExtension ) = $this->obtainSettings( $namedParameters );

        switch ( $operatorName )
        {
            case 'pdfquickpreview':
            {
                $ini = eZINI::instance( 'image.ini' );
                if( !$ini->variable( 'ImageMagick', 'IsEnabled' ) )
                {
                    eZDebug::writeError( 'ImageMagick is disabled. PDF quick preview requires it to be enabled', 'pdfquickpreview' );
                    $operatorValue = '';
                    return false;
                }

                $object = null;
                if( gettype( $operatorValue ) == 'object'  )
                {
                    if( get_class( $operatorValue ) === 'eZContentObjectTreeNode' )
                    {
                        $object = $operatorValue->object();
                    }
                    elseif( get_class( $operatorValue ) === 'eZContentObject' )
                    {
                        $object = $operatorValue;//->attribute( 'main_node' );
                    }

                }
                elseif( gettype( $operatorValue ) === 'array' )
                {
                    if( array_key_exists( 'node_id', $operatorValue ) )
                    {
                        // TODO:
                    }
                    elseif( array_key_exists( 'contentobject_id', $operatorValue ) )
                    {
                        // TODO:
                    }
                }

                $fileDataMap = $object->dataMap();
                $fileData = $fileDataMap['file'];
                $fileId = $fileData->ID;
                $fileVersion = $fileData->Version;
                $fileLanguageCode = $fileData->LanguageCode;
                $filePath = $fileData->content()->filePath();
                $fileOriginalFilename = $fileData->content()->OriginalFilename;
                $fileMimeType = $fileData->content()->MimeType;

                $output = array();
                if( $fileMimeType === 'application/pdf' )
                {
                    if( file_exists( $filePath ) )
                    {
                        $imageCacheDirPath = eZSys::cacheDirectory() . '/public/pdfquickpreview/' . $fileId . '-' . $fileVersion . '-' . $fileLanguageCode;
                        eZDir::mkdir( $imageCacheDirPath, false, true );
                        $pageFileName = $imageCacheDirPath . '/' . str_replace( '.', '_', $fileOriginalFilename ) . '_' . $variant;
                        if( !file_exists( $pageFileName . '-0.' . $fileExtension ) )
                        {
                            $output[] = $this->convert( $filePath, $pageFileName . '.' . $fileExtension );
                        }
                    }
                    else
                    {
                        eZDebug::writeError( 'PDF file ' . $filePath . ' cannot be found', 'pdfquickpreview' );
                        return false;
                    }

                    $imageList = array();
                    foreach( $pages as $page )
                    {
                        $pageFileName = $imageCacheDirPath . '/' . str_replace( '.', '_', $fileOriginalFilename ) . '_' . $variant . '-' . $page . '.' . $fileExtension;
                        if( file_exists( $pageFileName ) )
                        {
                            $imageList[] = $pageFileName;
                        }
                        else
                        {
                            eZDebug::writeError( 'File ' . $pageFileName . ' cannot be found', 'jh pdfquickpreview' );
                        }
                    }
                    $operatorValue = $this->render( $imageList );
                }
            } break;
        }
    }

    private function render( $images, $variant='default' )
    {
        // TODO: use templates for rendering output
        $output = '';

        foreach( $images as $image )
        {
            $output .= '<a href="/' . $image . '" class="fresco" data-fresco-group="unique_name"><img src="/' . $image . '" width="150"/></a>';
        }
        return $output;
    }

    private function convert( $pdfFilePath, $imageFilePath, $page=false )
    {
        $ini = eZINI::instance( 'image.ini' );
        $executablePath = $ini->hasVariable( 'ImageMagick', 'ExecutablePath' ) ? $ini->variable( 'ImageMagick', 'ExecutablePath' ) : false;
        $executable = $ini->hasVariable( 'ImageMagick', 'Executable' ) ? $ini->variable( 'ImageMagick', 'Executable' ) : false;

        if ( $executablePath )
        {
            $executable = $executablePath . eZSys::fileSeparator() . $executable;
        }
        if ( eZSys::osType() == 'win32' )
        {
            $executable = "\"$executable\"";
        }

        if( is_numeric( $page ) )
        {
            $command = $executable . ' ' . $pdfFilePath . '[' . ( $page - 1) . '] ' . $imageFilePath;
        }
        else
        {
            $command = $executable . ' ' . $pdfFilePath . ' ' . $imageFilePath;
        }
        $lastLine = system( $command, $retVal );
        eZDebug::writeNotice( $retVal, $command );
        return $command;
    }

    private function obtainPages( $params )
    {
        $explParams = explode( ',', $params );
        $pages = array();
        foreach( $explParams as $param )
        {
            $sp = explode( '..', $param );
            foreach( ( ( count( $sp ) > 1 ) ? range( $sp[0], $sp[1] ) : $sp ) as $p )
            {
                if( is_numeric( $p ) )
                {
                    $pages[] = (int)$p;
                }
            }
        }
        return $pages;
    }

    private function obtainSettings( $params )
    {
        $ini = eZINI::instance( 'pdfquickpreview.ini' );
        $variant = $params['variant'];
        $width = $ini->hasVariable( $variant, 'Width' ) ? $ini->variable( $variant, 'Width' ) : 300;
        $height = $ini->hasVariable( $variant, 'Height' ) ? $ini->variable( $variant, 'Height' ) : 300;
        $pages = $this->obtainPages( $params['pages'] );
        if( empty( $pages ) )
        {
            $pages = $ini->hasVariable( $variant, 'Pages' ) ? $ini->variable( $variant, 'Pages' ) : array();
        }
        $fileExtension = $ini->hasVariable( $variant, 'Extension' ) ? $ini->variable( $variant, 'Extension' ) : 'png';

        return array( $variant, $width, $height, $pages, $fileExtension );
    }
}

?>
