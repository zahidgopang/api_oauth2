<?php

function image_upload($request,$model,$file_name, $mb_file_size = 25)
{
    if(empty($model)) $model = 'user';
    if($request->file($file_name ))
    {
        $file = $request->file($file_name);
        return  file_save($file,$model, $mb_file_size);
    }
    return ['status' =>false,'link'=>null,'message' => 'Unable to upload file'];
}
function file_save($file,$model,$mb_file_size=25)
{
    try {
        $model = str_replace('/','',$model);
        //validateSize
        $precision = 2;
        $size = $file->getSize();
        $size = (int) $size;
        $base = log($size) / log(1024);
        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        $dSize = round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

        $aSizeArray = explode(' ', $dSize);
        if ($aSizeArray[0] > $mb_file_size && ($aSizeArray[1] == 'MB' || $aSizeArray[1] == 'GB' || $aSizeArray[1] == 'TB')) {
            return ['status' =>false,'link'=>null,'message' => 'Image size should be less than equal '.$mb_file_size.' MB'];
        }
        // rename & upload files to upload folder

        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($model,$fileName,'public');
        $image_url = $fileName;

        return ['status' =>true,'link'=>$image_url,'message' => 'file uploaded'];

    } catch (\Exception $e) {
        return ['status' =>false,'link'=> null ,'message' => $e->getMessage()];
    }
}
function ShowErrors($errors = [], $data = []){
    $error_message = '';
    $i = 1;
    foreach ($data as $key => $item) {
        if ($errors->has($key)) {
            $messages = $errors->get($key);
            foreach ($messages as $message) {
                $error_message = $message;
                $i++;
            }
        }
    }
    return $error_message;
}
function sendValidationError( $errors, $data ){
    return response()->json([
        'status' => false,
        'message' => ShowErrors($errors, $data),
        'errors' => $errors,
    ], 200);
}
function sendSuccessResponse( $msg , $response = array() ){
    return response()->json([
        'status' => true,
        'message' => $msg,
        'data' => $response,
    ], 200);
}
function sendError( $errors ){
    return response()->json([
        'status' => false,
        'message' => $errors,
    ], 200);
}
?>
