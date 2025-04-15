@props(['title'=>'']) 
<x-base-layout :$title >
  <x-layouts.header/>
  {{$slot}}
  <x-layouts.footer/> 

</x-base-layout>


