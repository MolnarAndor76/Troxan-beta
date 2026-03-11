    <div class="grid gap-0 grid-cols-5 h-[90vh] w-[90%] bg-white shadow-xl rounded-[5px] overflow-auto ">
            <div id="editor-left-content" class="gap-0 bg-amber-950 col-span-1">

            </div>
            <div id="editor-right-content" class="gap-0 bg-amber-200 col-span-4 overflow-auto">
                <div class="flex">
                <?php for( $i = 0; $i < 100; $i++) { ?>
                    <?php for( $y = 0; $y < 100; $y++) { ?>            
                    <div class='border border-solid aspect-square min-w-[50px] min-h-[50px]'></div>
                <?php }} ?>
            </div>
    </div>