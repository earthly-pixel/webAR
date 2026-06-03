declare module "mindar-offline-compiler" {
  export class OfflineCompiler {
    constructor();
    compileImageTargets(
      images: any[],
      progressCallback?: (progress: number) => void
    ): Promise<any>;
    exportData(): Buffer;
    importData(buffer: Buffer): any;
    createProcessCanvas(img: any): any;
    compileTrack(params: {
      progressCallback: (progress: number) => void;
      targetImages: any[];
      basePercent: number;
    }): Promise<any>;
    data: any;
  }
}
