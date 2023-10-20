@extends('layouts.base')
@section('content')
     <h1 class="h1 mt-3" style="text-align: center">Загрузка файлов</h1>
     <form  method="post" action="/upload" enctype="multipart/form-data">
         @csrf
         <div class="input-group row mt-3 justify-content-center">
             <div class="col-8">
                 <label for="file" class="form-label">Выберете файлы для загрузки</label>
                 <input type="file" name="file[]" id="file" class="form-control" multiple>
                 <input type="submit" class="btn btn-primary my-3">
             </div>
         </div>
     </form>
     <hr>
     <h1 class="h1"  style="text-align: center">Готовые файлы</h1>

     <div class="row justify-content-center">
         <ul class="list-group col-5">
             @foreach($files as $file)
                 <a class="list-group-item list-group-item-action a" href="/Download?file={{$file}}">{{ $file }}</a>
             @endforeach
         </ul>
     </div>
@endsection
