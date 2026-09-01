<?php

namespace App\interface;

interface borrowsInterface {
    public function allborrows();
    public function singleborrow($id);
    public function store($id);
    public function destroy($id);
}
