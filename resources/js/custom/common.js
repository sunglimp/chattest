$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

function getFileExtension(fileName) {
    // Use a regular expression to trim everything before final dot
    var extension = fileName.replace(/^.*\./, '');

    // If there is no dot anywhere in filename, we would have extension == filename,
    // so we account for this possibility now
    if (extension === fileName) {
        extension = '';
    } else {
        // if there is an extension, we convert to lower case on the file upload.
        extension = extension.toLowerCase();
    }
    return extension;
}