<?php
/**
 * Copyright (c) 2022 Yun Dou <dixyes@gmail.com>
 *
 * lwmbs is licensed under Mulan PSL v2. You can use this
 * software according to the terms and conditions of the
 * Mulan PSL v2. You may obtain a copy of Mulan PSL v2 at:
 *
 * http://license.coscl.org.cn/MulanPSL2
 *
 * THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS,
 * WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT,
 * MERCHANTABILITY OR FIT FOR A PARTICULAR PURPOSE.
 *
 * See the Mulan PSL v2 for more details.
 */

declare(strict_types=1);

namespace SPC\builder\macos\library;

class openssl extends MacOSLibraryBase
{
    public const NAME = 'openssl';

    protected function build()
    {
        [$lib,,$destdir] = SEPARATED_PATH;

        // lib:zlib
        $extra = '';
        $ex_lib = '';
        $zlib = $this->builder->getLib('zlib');
        if ($zlib instanceof MacOSLibraryBase) {
            $extra = 'zlib';
            $ex_lib = trim($zlib->getStaticLibFiles() . ' ' . $ex_lib);
        }

        shell()->cd($this->source_dir)
            ->exec(
                "{$this->builder->configure_env} ./Configure no-shared {$extra} " .
                '--prefix=/ ' . // use prefix=/
                "--libdir={$lib} " .
                " darwin64-{$this->builder->arch}-cc"
            )
            ->exec('make clean')
            ->exec("make -j{$this->builder->concurrency} CNF_EX_LIBS=\"{$ex_lib}\"")
            ->exec("make install_sw DESTDIR={$destdir}");
    }
}
