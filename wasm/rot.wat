(module
 (func $abs (param $n i32) (result i32)
  get_local $n
  i32.const 31
  i32.shr_s
  get_local $n
  i32.add
  get_local $n
  i32.const 31
  i32.shr_s
  i32.xor
 )
 
 (func $within (param $in i32) (param $a i32) (param $b i32) (result i32)
    get_local $in
    get_local $a
    i32.gt_u
    get_local $in
    get_local $b
    i32.lt_u
    i32.and
 )

 (func $_rot (param $byte i32) (param $offset i32) (param $n i32) (result i32)
    get_local $byte
    get_local $offset
    i32.sub
    get_local $n
    i32.add
    i32.const 26
    i32.rem_u
    get_local $offset
    i32.add    
 )

 (func $rot (param $byte i32) (param $n i32) (result i32)
  get_local $n
  call $abs
  i32.const 26
  i32.rem_u
  set_local $n
  get_local $byte
  i32.const 64
  i32.const 91
  call $within
  if
    get_local $byte
    i32.const 65
    get_local $n
    call $_rot
    set_local $byte
  else
    get_local $byte
    i32.const 96
    i32.const 123
    call $within
    if
      get_local $byte
      i32.const 97
      get_local $n
      call $_rot
      set_local $byte
    end
  end
  get_local $byte
  )
 (export "rot" (func $rot))
)