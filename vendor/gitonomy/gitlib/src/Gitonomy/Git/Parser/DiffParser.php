
        $this->files = [];
            $vars = $this->consumeRegexp("/diff --git \"?(a\/.*?)\"? \"?(b\/.*?)\"?\n/");
                //verifying if the file was deleted or created
                    $oldName = $this->consumeTo("\n") === '/dev/null' ? '/dev/null' : $oldName;
                    $newName = $this->consumeTo("\n") === '/dev/null' ? '/dev/null' : $newName;
                    $vars = $this->consumeRegexp('/"?(.*?)"? and "?(.*?)"? differ\n/');

            $oldIndex = $oldIndex !== null ?: '';
            $newIndex = $newIndex !== null ?: '';
                $rangeOldStart = (int) $vars[1];
                $rangeOldCount = (int) $vars[2];
                $rangeNewStart = (int) $vars[3];
                $rangeNewCount = isset($vars[4]) ? (int) $vars[4] : (int) $vars[2]; // @todo Ici, t'as pris un gros raccourci mon loulou
                $lines = [];
                        $lines[] = [FileChange::LINE_CONTEXT, $this->consumeTo("\n")];
                        $lines[] = [FileChange::LINE_ADD, $this->consumeTo("\n")];
                        $lines[] = [FileChange::LINE_REMOVE, $this->consumeTo("\n")];